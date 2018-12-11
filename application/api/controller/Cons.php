<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\Appointment;
use app\common\model\Consultation;
use app\common\model\ConsultationProfile;
use app\common\model\Coupon;
use app\common\model\Doctor;
use app\common\model\Finance;
use app\common\model\MessageDoctor;
use app\common\model\Order;
use app\common\model\Service;
use app\common\model\User;
use app\common\model\UserCoupon;
use think\Db;

class Cons extends Base
{
    public function currentImageCon()
    {
        $consultationModelList = Consultation::selectCurrentCon(
            $this->getUserId(),
            '图文咨询',
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function currentPhoneCon()
    {
        $consultationModelList = Consultation::selectCurrentCon(
            $this->getUserId(),
            '电话咨询',
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function currentVideoCon()
    {
        $consultationModelList = Consultation::selectCurrentCon(
            $this->getUserId(),
            '视频咨询',
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function currentPrivateService()
    {
        $consultationModelList = Consultation::selectCurrentPrivate(
            $this->getUserId(),
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function uploadProfile()
    {
        $keys = [
            'department', 'content', 'name', 'age', 'gender', 'blood_type',
            'is_born', 'born_time', 'born_type', 'is_allergy', 'allergy', 'smoke', 'drink',
            'operation_history', 'has_genetic_disease', 'genetic_disease',
            'semen_volume', 'semen_density', 'masturbation_history', 'abstinent_days', 'prepare_pregnant_time',
        ];
        $requestData = $this->selectParam($keys);
        foreach ($requestData as $key => $value) {
            if (is_null($value)) {
                $requestData[$key] = '';
            }
        }
        // todo: 验参

        $cpModel = ConsultationProfile::create(array_merge($requestData, ['c_id' => $this->getUserId()]));
        if (empty($cpModel)) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail");
            return $this->getRenderJson();
        }

        $this->addRenderData('cp_id', $cpModel['cp_id']);
        return $this->getRenderJson();
    }

    public function addImageCon()
    {
        $requestData = $this->selectParam(['doctor_id', 'money', 'cp_id', 'pay_type', 'coupon_id', 'ex_con_id']);
        $this->check($requestData, 'Cons.add_image_con');
        $doctorId = $requestData['doctor_id'];

        // 检查id, 是否已经结束上次问诊, 优惠券, 金额

        // check doctor
        $doctorModel = Doctor::get($doctorId);
        $checkDoctorResult = Doctor::checkDoctor($doctorModel, 'image');
        if ($checkDoctorResult !== true) {
            $this->setRenderCode(400);
            $this->setRenderMessage($checkDoctorResult);
            return $this->getRenderJson();
        }
        $price = $doctorModel['image_price'];

        // check con
        $conWhereMap = [
            'c_id' => $this->getUserId(),
            'd_id' => $doctorId,
            'type' => '图文咨询',
            'state' => ['in', ['未进行', '进行中']],
        ];
        $conModel = Consultation::build()->where($conWhereMap)->find();
        if (!empty($conModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('存在未结束的咨询');
            return $this->getRenderJson();
        }

        // 检查优惠券
        $isUseCoupon = false;
        if (!empty($requestData['coupon_id'])) {
            $checkCouponResult = UserCoupon::checkCoupon(
                $requestData['coupon_id'],
                $this->getUserId(),
                $requestData['doctor_id'],
                $price
            );
            if ($checkCouponResult === false) {
                $isUseCoupon = false;
                $requestData['coupon_id'] = 0;
            } else {
                $isUseCoupon = true;
                $price = $checkCouponResult;
            }
        }

        // 检查转诊
        // todo

        // 检查余额
        if ($requestData['pay_type'] == 'balance') {
            $userModel = $this->getUserModel();
            if ($price > $userModel['money']) {
                $this->setRenderCode(400);
                $this->setRenderMessage('账户余额不足');
                return $this->getRenderJson();
            }
        }

        $extraData = [
            'cp_id' => $requestData['cp_id'],
            'couponId' => $requestData['coupon_id'],
            'exConId' => $requestData['ex_con_id'],
            'during' => '0',
            'time' => time(),
        ];

        trans_start();

        // 新建订单信息
        $orderData = Order::addUserOrder(
            $this->getUserId(),
            $doctorId,
            'image',
            $requestData['pay_type'],
            $price,
            $extraData
        );
        $orderId = $orderData['or_id'];

        // 处理优惠券
        if ($isUseCoupon) {
            UserCoupon::setCouponUsed($requestData['coupon_id']);
        }

        if ($requestData['pay_type'] == 'balance') {
            // 余额支付 处理状态 返回数据
            $completeData = Order::completeOrder($orderId, $this->getUserId(), false);
            if ($completeData === false) {
                trans_rollback();
                $this->setRenderCode(500);
                $this->setRenderMessage('无法完成订单');
                return $this->getRenderJson();
            }
            $this->addRenderData('consultation', $completeData);
        }

        trans_commit();
        $this->addRenderData('order_info', $orderData);

        return $this->getRenderJson();
    }

    public function addAppointment()
    {

            $requestData = $this->selectParam([ 'did', 'money', 'con_type', 'time', 'during', 'cp_id' ]);
//        $this->check(); // todo: during between 10 30

        // 检查是否有开通私人医生服务
        $serviceId = Service::checkHasPrivateService(
            $this->getUserId(),
            $requestData['did'],
            $requestData['time'] // 预约时间
        );
        if (!empty($serviceId)) {
            $isFree = true;
        } else {
            $isFree = false;
        }

        // 检查id, 是否已经结束上次问诊, 优惠券, 金额
        $userModel = $this->getUserModel();

        if ($requestData['con_type'] == 'phone' && empty($userModel['mobile'])) {
            $this->setRenderCode(400);
            $this->setRenderMessage('尚未设置手机号码');
            return $this->getRenderJson();
        }

        if ($requestData['con_type'] == 'phone') {
            $appModel = Appointment::build()->where([
                'c_id' => $this->getUserId(),
                'type' => '电话咨询',
                'status' => ['neq', 'no'],
                'appoint_time' => $requestData['time'],
            ])->find();
            if (!empty($appModel)) {
                $this->setRenderCode(400);
                $this->setRenderMessage('改时间段已存在电话咨询，请勿重复发起');
                return $this->getRenderJson();
            }
        }

        // 医生信息
        $doctorField = [ 'doctor_id', 'is_open_phone', 'is_open_video', 'phone_price', 'video_price' ];
        $doctorWhereMap = [ 'doctor_id' => $requestData['did'], 'audit_status' => 'yes' ];
        $doctorModel = Doctor::build()->field($doctorField)->where($doctorWhereMap)->find();
        if (empty($doctorModel)) {
            // 无效医生 id 或医生未通过验证
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该医生');
            return $this->getRenderJson();
        }
        $isOpen = 'is_open_' . $requestData['con_type'];
        $priceType = $requestData['con_type'] . '_price';
        $price = $doctorModel[$priceType] * $requestData['during'];
        if ($doctorModel[$isOpen] == 'no' || empty($price)) {
            // 医生未开通该服务 or 未设置价格
            $this->setRenderCode(400);
            $this->setRenderMessage('医生未开通该服务');
            return $this->getRenderJson();
        }

        if ($isFree) {
            $price = 0;
            $checkBalance = false;
        } else {
            if ($requestData['money'] != $price) {
                // 价格错误
                $this->setRenderCode(400);
                $this->setRenderMessage('价格错误');
                return $this->getRenderJson();
            }
            // 检查余额
            if ($price > $userModel['money']) {
                $this->setRenderCode(400);
                $this->setRenderMessage('账户余额不足');
                return $this->getRenderJson();
            }
        }

        // todo: 验证是否可预约, 十分钟前不可再预约

        // 新建订单信息
        $extraData = [
            'cp_id' => $requestData['cp_id'],
            'during' => $requestData['during'],
            'time' => $requestData['time'],
            'seId' => $serviceId,
        ];
        $orderData = Order::addUserOrder(
            $this->getUserId(),
            $requestData['did'],
            $requestData['con_type'],
            'balance',
            $price,
            $extraData
        );
        $orderId = $orderData['or_id'];

        // 余额支付 处理状态 返回数据
        $completeData = Order::completeOrder($orderId, $this->getUserId(), false);
        if ($completeData === false) {
            trans_rollback();
            $this->setRenderCode(500);
            $this->setRenderMessage('无法完成订单');
            return $this->getRenderJson();
        }
        $this->addRenderData('consultation', $completeData);
        trans_commit();
        $this->addRenderData('order_info', $orderData);

        return $this->getRenderJson();
    }

    public function addPrivateAppointment()
    {
        $requestData = $this->selectParam([ 'did', 'pay_type', 'money', 'private_during', 'cp_id' ]);
//        $this->check(); // todo:
//            if (!validate_words($private_during, ['a', 'b', 'c', 'd', 'e'])) {

        $useBalance = ($requestData['pay_type'] == 'balance') ? true : false;

        $serviceWhereMap = [
            'c_id' => $this->getUserId(),
            'd_id' => $requestData['did'],
            'status' => [ 'in', [ 'yes', 'wait' ] ],
        ];
        $serviceModel = Service::build()->where($serviceWhereMap)->find();
        if (!empty($serviceModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('已有正在进行中的服务');
            return $this->getRenderJson();
        }

        // 医生信息
        $doctorField = [ 'doctor_id', 'is_open_private', 'private_price' ];
        $doctorWhereMap = [ 'doctor_id' => $requestData['did'], 'audit_status' => 'yes' ];
        $doctorModel = Doctor::build()->field($doctorField)->where($doctorWhereMap)->find();
        if (empty($doctorModel)) {
            // 无效医生 id 或医生未通过验证
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该医生');
            return $this->getRenderJson();
        }
        $doctorPriceMap = customUnserialize($doctorModel['private_price']);
        $price = $doctorPriceMap[$requestData['private_during']]; // todo
        if ($doctorModel['is_open_private'] == 'no' || empty($price)) {
            // 医生未开通该服务 or 未设置价格
            $this->setRenderCode(400);
            $this->setRenderMessage('尚未设置手机号码');
            return $this->getRenderJson();
        }

        if ($requestData['money'] != $price) {
            // 价格错误
            $this->setRenderCode(400);
            $this->setRenderMessage('价格错误');
            return $this->getRenderJson();
        }

        // 检查余额
        $userModel = $this->getUserModel();
        if ($useBalance && $price > $userModel['money']) {
            $this->setRenderCode(400);
            $this->setRenderMessage('账户余额不足');
            return $this->getRenderJson();
        }

        // 新建订单信息
        $extraData = [
            'cp_id' => $requestData['cp_id'],
            'during' => $requestData['private_during'],
            'time' => time(),
            'real_money' => $price,
        ];
        $orderData = Order::addUserOrder(
            $this->getUserId(),
            $requestData['did'],
            'private',
            $requestData['pay_type'],
            $price,
            $extraData
        );
        $orderId = $orderData['or_id'];

        if ($requestData['pay_type'] == 'balance') {
            // 余额支付 处理状态 返回数据
            $completeData = Order::completeOrder($orderId, $this->getUserId(), false);
            if ($completeData === false) {
                trans_rollback();
                $this->setRenderCode(500);
                $this->setRenderMessage('无法完成订单');
                return $this->getRenderJson();
            }
            $this->addRenderData('consultation', $completeData);
        }

        trans_commit();
        $this->addRenderData('order_info', $orderData);

        return $this->getRenderJson();
    }

    public function addComment()
    {
        $requestData = $this->selectParam(['con_id', 'did', 'grade', 'impression', 'good_at', 'evaluation']);
        // todo: $grade, '/^(很满意|满意|不满意)$/'
        // $this->check('');

        $consModel = Consultation::build()
            ->where([ 'con_id' => $requestData['con_id'], 'c_id' => $this->getUserId(), 'd_id' => $requestData['did'] ])
            ->find();
        if (empty($consModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该咨询');
            return $this->getRenderJson();
        }
        if ($consModel['state'] != '已完成') {
            $this->setRenderCode(400);
            $this->setRenderMessage('该咨询暂时无法评价');
            return $this->getRenderJson();
        }
        if (!empty($consModel['comment_time'])) {
            $this->setRenderCode(400);
            $this->setRenderMessage('已经评价过了哦');
            return $this->getRenderJson();
        }

        $updateData = [
            'grade' => $requestData['grade'],
            'impression' => $requestData['impression'],
            'doc_good_at' => $requestData['good_at'],
            'evaluation' => $requestData['evaluation'],
            'comment_time' => strval(time()),
        ];
        $consModel->isUpdate(true)->save($updateData);

        Doctor::updateStatisticData($requestData['did'], $requestData['good_at'], $requestData['impression']);
        $doctorModel = Doctor::get($requestData['did']);

        $userModel = $this->getUserModel();
        $msgExtraInfo = [
            'event_type' => '评价',
            'event_id' => $requestData['con_id'],
            'sub_type' => '评价',
            'username' => $userModel['nick_name'],
            'gender' => $userModel['gender'],
            'avatar' => $userModel['avatar'],
            'easemob_username' => $userModel['easemob_username'],
        ];
        MessageDoctor::pushSystemMessage(
            $requestData['did'],
            "患者评价了你的咨询，请点击查看",
            $msgExtraInfo,
            0,
            $requestData['con_id'],
            'system',
            $doctorModel['easemob_username']
        );

        $this->setRenderMessage('评论成功');
        return $this->getRenderJson();
    }

    public function historyCon()
    {
        $conType = $this->getParam('con_type');
        // todo:
//        $this->checkSingle($conType, '', '');

        $conTypeMap = [
            'image' => '图文咨询',
            'phone' => '电话咨询',
            'video' => '视频咨询',
        ];

        $consultationModelList = Consultation::selectHistoryCon(
            $this->getUserId(),
            $conTypeMap[$conType],
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function historyPrivateService()
    {
        $consultationModelList = Consultation::selectHistoryPrivate(
            $this->getUserId(),
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function checkRecord()
    {
        $requestData = $this->selectParam(['did', 'con_type']);
        if (empty($requestData['con_type'])) {
            $requestData['con_type'] = 'image';
        }
        //todo:
//        $this->check($requestData, '');
        $conTypeMap = [
            'image' => '图文咨询',
            'phone' => '电话咨询',
            'video' => '视频咨询',
        ];
        $fieldList = [ 'con_id', 'c_id', 'd_id', 'state', 'type', 'service_id' ];
        $whereMap = [
            'c_id' => $this->getUserId(),
            'd_id' => $requestData['did'],
            'type' => $conTypeMap[$requestData['con_type']],
        ];
        $orderMap = [ 'create_time' => 'desc'];
        $conModel = Consultation::build()->field($fieldList)->where($whereMap)->order($orderMap)->find();

        $whereMap['service_id'] = ['neq', '0'];
        $whereMap['state'] = '进行中';
        $conServiceModel = Consultation::build()->field($fieldList)->where($whereMap)->select();

        $this->addRenderData('consultation', $conModel);
        $this->addRenderData('has_private', empty($conServiceModel) ? 'no' : 'yes');

        return $this->getRenderJson();
    }

    public function appointmentList()
    {
        $appointModelList = Appointment::getMyAppoint($this->getUserId(), '', $this->pageIndex, $this->pageSize);

        $this->addRenderData('appointment', $appointModelList, false);
        return $this->getRenderJson();
    }

    public function consInService()
    {
        $conType = $this->getParam('con_type');
        $serviceId = $this->getParam('se_id');
        // todo:
//        $this->checkSingle($conType, '', '');

        // todo:
        // 验证这个service是不是自己的

        $conTypeMap = [
            'phone' => '电话咨询',
            'video' => '视频咨询',
        ];

        $consultationModelList = Consultation::selectConInService(
            $this->getUserId(),
            $serviceId,
            $conTypeMap[$conType],
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('consultation_list', $consultationModelList, false);

        return $this->getRenderJson();
    }

    public function cancel()
    {
        $conId = $this->getParam('con_id');
        $reason = $this->getParam('reason');
        $this->checkSingle($conId, 'id', 'Base.id');

        $userModel = $this->getUserModel();

        $conModel = Consultation::build()->where([ 'con_id' => $conId, 'c_id' => $this->getUserId() ])->find();
        if (empty($conModel) || ($conModel['type'] != '电话咨询' && $conModel['type'] != '视频咨询')) {
            $this->setRenderCode(400);
            $this->setRenderMessage('无效的咨询信息');
            return $this->getRenderJson();
        }

        $ap_id = $conModel['ap_id'];

        $whereMap = [
            'ap_id' => $ap_id,
            'status' => 'yes',
        ];
        $appointModel = Appointment::build()->where($whereMap)->find();
        if (empty($appointModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('无效的预约信息');
            return $this->getRenderJson();
        }
        if ($appointModel['appoint_time'] < time() - 60 * 10) {
            $this->setRenderCode(400);
            $this->setRenderMessage('咨询即将开始, 无法取消');
            return $this->getRenderJson();
        }

        $current = time();

        Db::startTrans();
        $updateCon = Consultation::update(
            [ 'state' => '已取消' ],
            [ 'con_id' => $conId ]
        );
        // 预约状态改为no, 用户余额加回去, 订单加上退款
        $goodsType = ($conModel['type'] == '电话咨询') ? 'phone' : 'video';

        $updateAppoint = Appointment::update(
            [ 'status' => 'no', 'reason' => $reason,],
            [ 'ap_id' => $ap_id ]
        );
        $updateOrder = Order::update(
            [ 'is_refund' => 'yes', 'refund_time' => $current ],
            [ 'goods_id' => $ap_id, 'goods_type' => $goodsType, 'is_refund' => 'no' ]
        );
        $updateUser = User::build()
            ->where([ 'user_id' => $this->userId ])->inc('money', $appointModel['price'])->update();

        if (!$updateCon || !$updateAppoint || !$updateOrder || $updateUser === false) {
            Db::rollback();
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            return $this->getRenderJson();
        }

        //插入交易记录
        $extra = json_encode(
            [ "ap_id" => $appointModel['ap_id'], 'type' => $appointModel['type'] ],
            JSON_UNESCAPED_UNICODE
        );
        $financeModel =Finance::create([
            'user_id' => $this->getUserId(),
            'user_type' => 'customer',
            'money' => $appointModel['price'],
            'type' => 'refund',
            'status' => 'in',
            'extra' => $extra
        ]);
        if (empty($financeModel)) {
            Db::rollback();
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            return $this->getRenderJson();
        }

        $doctorModel = Doctor::get($appointModel['d_id']);

        $username = $userModel['nick_name'];
        $msgExtraInfo = [
            'event_type' => $conModel['type'],
            'event_status' => '已取消',
            'event_id' => $conId,
            'is_appoint' => '0',
            'sub_type' => $conModel['type'],
            'username' => $username,
            'gender' => $userModel['gender'],
            'avatar' => $userModel['avatar'],
            'easemob_username' => $userModel['easemob_username'],
        ];
        $reason = empty($reason) ? "患者" . $userModel['nick_name'] . "取消了" . $appointModel['type'] . "预约" : $reason;
        $message_content = "患者" . $userModel['nick_name'] . "取消了与您的" . $appointModel['type'] . "预约";
        MessageDoctor::pushMessage(
            $appointModel['d_id'],
            $message_content,
            $msgExtraInfo,
            $this->getUserId(),
            $conId,
            'con',
            $userModel['easemob_username'],
            $doctorModel['easemob_username']
        );

        MessageDoctor::pushMessage(
            $appointModel['d_id'],
            $reason,
            $msgExtraInfo,
            $this->getUserId(),
            $conId,
            'con',
            $userModel['easemob_username'],
            $doctorModel['easemob_username']
        );

        Db::commit();

        $this->setRenderMessage('取消成功');
        return $this->getRenderJson();
    }

    public function getAppointStatus()
    {
        $apId = $this->getParam('ap_id');
        $this->checkSingle($apId, 'id', 'Base.id');

        $appoint = Appointment::getAppointDetail($apId, $this->getUserId());

        $this->addRenderData('appointment', $appoint);
        return $this->getRenderJson();
    }
    
    public function getProfile()
    {
        $cpId = $this->getParam('cp_id');
        $this->checkSingle($cpId, 'id', 'Base.id');

        $profileModel = ConsultationProfile::build()->where([
            'cp_id' => $cpId,
            'c_id' => $this->getUserId(),
        ])->find();

        if (empty($profileModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该病历信息');
            return $this->getRenderJson();
        }

        $this->addRenderData('profile', $profileModel);
        return $this->getRenderJson();
    }

    public function getValidTime()
    {
        $conId = $this->getParam('con_id');
        $this->checkSingle($conId, 'id', 'Base.id');

        $conModel = Consultation::build()->field(['con_id', 'valid_time'])->where(['con_id' => $conId])->find();

        if (empty($conModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该咨询信息');
            return $this->getRenderJson();
        }

        $this->addRenderData('con_id', $conModel['con_id']);
        $this->addRenderData('valid_time', $conModel['valid_time']);

        return $this->getRenderJson();
    }

    public function updateVideoInfo()
    {
        $resultData = $this->selectParam([ 'con_id', 'call_time', 'status' ]);
//        $this->check(); // todo:

        $conModel = Consultation::get($resultData['con_id']);
        if (empty($conModel) || $conModel['type'] != '视频咨询') {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到该咨询信息');
            return $this->getRenderJson();
        }

        $newCallData = [
            'status' => $resultData['status'],
            'callTime' => $resultData['call_time'],
        ];

        $callData = unserialize($conModel['call_data']); // 通话记录组
        if (empty($callData)) {
            $newCallData['count'] = '1';
        } else {
            $lastCallData = end($callData); // 最后一条通话记录
            $newCallData['count'] = empty($lastCallData['count']) ? '1' : ($lastCallData['count'] + 1) . ""; // 最后一条的值
        }

        $callData[] = $newCallData;
        $newValidTime = $conModel['valid_time'] - $resultData['call_time'];
        $updateData = [
            'valid_time' => ($newValidTime >= 0) ? strval($newValidTime) : '0',
            'call_data' => serialize($callData),
        ];


        // 保存通话信息
        $saveResult = Consultation::update($updateData, [ 'con_id' => $resultData[ 'con_id' ] ]);

        if ($saveResult === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            return $this->getRenderJson();
        }

        $this->addRenderData('con_id', $resultData['con_id']);
        $this->addRenderData('count', strval($newCallData['count']));
        $this->addRenderData('valid_time', $updateData['valid_time']);

        return $this->getRenderJson();
    }
}
