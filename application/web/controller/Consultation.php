<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/25/16
 * Time: 10:14
 */

namespace app\web\controller;

use app\index\controller\Base;
use app\index\validate\Profile;
use app\web\logic\ConsultationLogic;
use app\web\logic\CustomerLogic;
use app\web\logic\DoctorLogic;
use app\index\model\Extend;
use app\web\logic\OrderLogic;
use app\web\model\CustomerCoupon;

class Consultation extends Base
{

    /**
     * 用户获取自己的咨询记录
     * @return \think\response\Json
     */
    public function getRecord()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $tab = get_post_value('tab', 'current');
        $type = get_post_value('type', 'all');
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');
        $validateResult = validate_number($page) &&
            validate_number($num) &&
            validate_regex($tab, '/^(current|history)$/') &&
            validate_regex($type, '/^(all|image|video|phone)$/');
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getRecord($cid, $tab, $type, $page, $num);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);

    }

    public function getSuperRecord()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $tab = get_post_value('tab', 'current');
        $type = get_post_value('type', '全部服务');
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');

        $validateResult = validate_number($page) &&
                          validate_number($num) &&
                          validate_words($tab, ['current', 'history']) &&
                          validate_words($type, ['全部服务', '图文咨询', '电话咨询', '视频咨询', '私人医生', '院后指导']);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getSuperRecord($cid, $tab, $type, $page, $num);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    /**
     * 用户检查对某医生 是否存在问诊
     * @return \think\response\Json
     */
    public function checkRecord()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', 'current');
        $type = get_post_value('type', '图文咨询');

        $validateResult = (validate_number($did)) &&
            (validate_regex($type, '/^(图文咨询|电话咨询|视频咨询)$/'));
        if (!$validateResult) {
            if (empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->checkRecord($cid, $did, $type);
        if (!$result) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }
        return $this->private_result(RESPONSE_SUCCESS, $result);

    }

    public function checkCallData()
    {
        // 验token并返回id
        $userInfo = $this->checkTokenAndGetUser();
        if ($userInfo == false) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $conId = get_post_value('con_id');
        $validateResult = (validate_number($conId));
        if (!$validateResult) {
            if (empty($conId)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getCallData($conId, $userInfo['id'], $userInfo['type']);
        if (!$result) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }
        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    /**
     * 用户评论医生
     * @return \think\response\Json
     */
    public function comment()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', '');
        $conId = get_post_value('con_id', '');
        $grade = get_post_value('grade', '');
        $impression = replace_separator(get_post_value('impression', ''));
        $goodAt = replace_separator(get_post_value('good_at', ''));
        $evaluation = safe_str(get_post_value('evaluation', ''));

        $data = [
            'grade' => $grade,
            'impression' => $impression,
            'doc_good_at' => $goodAt,
            'evaluation' => $evaluation,
        ];

        $validateResult = (validate_number($did)) &&
            (validate_number($conId)) &&
            (validate_regex($grade, '/^(很满意|满意|不满意)$/')) &&
            ($this->checkImpression($impression)) &&
            ($this->checkDocGoodAt($did, $goodAt));
        if (!$validateResult) {
            if (empty($did) || empty($conId)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->comment($cid, $conId, $data);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        $doctor = new DoctorLogic();
        $doctor->updateStatisticData($did, $data);

        return $this->private_result(RESPONSE_SUCCESS);
    }

    /**
     * 发起图文咨询
     * @return \think\response\Json
     */
    public function addImageConsultation()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 必要参数
        $did = get_post_value('did', '');
        $payType = get_post_value('pay_type'); // 用户支付类型 balance、alipay、wechat
        $money = get_post_value('money'); // 用户实际支付金额
        $profile = $this->getProfileData(); // 用户病历

        // 非必要参数
        $couponId = get_post_value('coupon_id', '0'); // 使用的优惠券id
        $exConId = get_post_value('ex_con_id', '0'); // 转诊用, 上个问诊id

        // 验参
        $validateResult = validate_number($did) &&
                          validate_words($payType, ['balance', 'alipay', 'wechat']) &&
                          validate_number($money) &&
                          validate_number($couponId) &&
                          validate_number($exConId) &&
                          $this->checkProfileData($profile); // 验病历模板
        if (!$validateResult) {
            if (empty($did) || empty($payType) || empty_without_zero($money)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        $useBalance = ($payType == 'balance') ? true : false;

        // 检查id, 是否已经结束上次问诊, 优惠券, 金额
        $consultation = new ConsultationLogic();
        $prepareResult = $consultation->prepareNewConsultation($cid, $did, 'image', $money, $couponId, $useBalance);
        if ($prepareResult !== true) {
            if (in_array($prepareResult, $this->errKeys)) {
                return $this->private_result($prepareResult);
            } else {
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "未通过前置检查");
            }
        }

        $extraData = [
            'profile' => $profile,
            'couponId' => $couponId,
            'exConId' => $exConId,
            'during' => '0',
            'time' => time(),
        ];

        trans_start();

        // 新建订单信息
        $order = new OrderLogic();
        $orderData = $order->addOrder($cid, $did, 'image', $payType, $money, $extraData);
        if ($orderData === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "新建订单失败");
        }
        $orderId = $orderData['or_id'];

        // 处理优惠券
        if (!empty($couponId)) {
            $useConResult = $consultation->updateCoupon($couponId, $cid);
            if ($useConResult === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "优惠券处理失败");
            }
        }

        if ($useBalance) {
            // 余额支付 处理状态 返回数据
            $completeData = $order->completeOrder($orderId, $cid);
            if ($completeData === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "完成订单失败");
            }
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, array_merge($orderData, $completeData));

        } else {
            // 充值支付 返回数据
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, $orderData);
        }

    }

    public function addAppointment()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 必要参数
        $did = get_post_value('did', '');
        $money = get_post_value('money'); // 用户实际支付金额
        $type = get_post_value('type'); // 咨询类型
        $time = get_post_value('time'); // 预约时间  时间戳
        $during = get_post_value('during'); // 预约时长 min
        $profile = $this->getProfileData(); // 用户病历

        // 非必要参数
        //$couponId = get_post_value('coupon_id', '0'); // 使用的优惠券id , 暂时不开放
        $couponId = '0';

        // 验参
        $validateResult = validate_number($did) &&
                          validate_number($money) &&
                          validate_words($type, ['电话咨询', '视频咨询']) &&
                          validate_number($time) &&
                          validate_number($during) &&
                          validate_number($couponId) &&
                          $this->checkProfileData($profile); // 验病历模板
        if (!$validateResult) {
            if (empty($did) || empty_without_zero($money) || empty($type) || empty($time) || empty($during)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        if ($during < 10 || $during > 30) {
            return $this->private_result(RESPONSE_FAIL_WRONG_APPOINTMENT_DURING);
        }

        $conType = ($type == '电话咨询') ? 'phone' : 'video';

        $consultation = new ConsultationLogic();

        // 检查是否有开通服务, 检查时间是否到期
        $seId = 0;
        $isFree = $consultation->checkService($cid, $did, $time, $seId);
        if ($isFree && !empty($seId)) {
            $money = 0;
        }

        // 检查id, 是否已经结束上次问诊, 优惠券, 金额
        $prepareResult = $consultation->prepareNewConsultation($cid, $did, $conType, $money, $couponId, true, $during, $isFree);
        if ($prepareResult !== true) {
            if (in_array($prepareResult, $this->errKeys)) {
                return $this->private_result($prepareResult);
            } else {
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "未通过前置检查");
            }
        }

        // 验证是否可预约, 十分钟前不可再预约
        $prepareResult2 = $consultation->prepareNewAppointment($did, $time);
        if ($prepareResult2 !== true) {
            return $this->private_result(RESPONSE_FAIL_WRONG_TIME);
        }

        $extraData = [
            'profile' => $profile,
            'couponId' => $couponId,
            'exConId' => '0',
            'during' => $during,
            'time' => $time,
            'seId' => $seId,
        ];

        trans_start();

        // 新建订单信息
        $order = new OrderLogic();
        $orderData = $order->addOrder($cid, $did, $conType, 'balance', $money, $extraData);
        if ($orderData === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "新建订单失败");
        }
        $orderId = $orderData['or_id'];

        // 处理优惠券
        if (!empty($couponId)) {
            $useConResult = $consultation->updateCoupon($couponId, $cid);
            if ($useConResult === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "优惠券处理失败");
            }
        }

        // 余额支付 处理状态 返回数据
        $completeData = $order->completeOrder($orderId, $cid);
        if ($completeData === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "完成订单失败");
        }
        trans_commit();
        return $this->private_result(RESPONSE_SUCCESS, array_merge($orderData, $completeData));
    }

    public function getValidTime()
    {
        // 验token并返回id
        $userInfo = $this->checkTokenAndGetUser();
        if ($userInfo == false) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $conId = get_post_value('con_id');
        if (empty($conId)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        if (!validate_number($conId)) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        // todo: 不安全, 需要检查 咨询-医生 关联状态
        $info = $consultation->getDetail($conId);

        if ($info === false) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

        $resultData = [
            'con_id' => $info['con_id'] . "",
            'valid_time' => $info['valid_time'] . "",
        ];

        return $this->private_result(RESPONSE_SUCCESS, $resultData);
    }

    public function updateVideoInfo()
    {
        // 验token并返回id
        $userInfo = $this->checkTokenAndGetUser();
        if ($userInfo == false) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $conId = get_post_value('con_id');
        $callTime = get_post_value('call_time', '0'); // seconds
        $status = get_post_value('status');

        $validateResult = validate_number($conId) &&
                          validate_number($callTime) &&
                          validate_words($status, ['calling', 'end']);
        if (!$validateResult) {
            if (empty($conId) || empty($callId) || empty_without_zero($callTime) || empty($status)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        $consultation = new ConsultationLogic();
        $info = $consultation->getDetail($conId);

        if (($info === false) || ($info['type'] != '视频咨询')) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }
        if ($userInfo['type'] == 'customer') {
            if ($info['c_id'] != $userInfo['id']) {
                return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
            }
        } else {
            if ($info['d_id'] != $userInfo['id']) {
                return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
            }
        }

        $newCallData = [
            'status' => $status,
            'callTime' => $callTime,
        ];

        $callData = unserialize($info['call_data']); // 通话记录组
        if (empty($callData)) {
            $newCallData['count'] = '1';
        } else {
            $lastCallData = end($callData); // 最后一条通话记录
            $newCallData['count'] = empty($lastCallData['count']) ? '1' : ($lastCallData['count'] + 1) . ""; // 最后一条的值
        }

        $callData[] = $newCallData;
        $updateData = [
            'valid_time' => $info['valid_time'] - $callTime,
            'call_data' => serialize($callData),
        ];

        // 保存通话信息
        $saveResult = $consultation->saveData($conId, $updateData);

        if ($saveResult === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $resultData = [
            'con_id' => $conId . "",
            'count' => $newCallData['count'] . "",
            'valid_time' => $updateData['valid_time'] . "",
        ];
        return $this->private_result(RESPONSE_SUCCESS, $resultData);
    }

    /**
     * 预约 私人医生/院后指导
     */
    public function createService()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }


        // 必要参数
        $did = get_post_value('did', '');
        $payType = get_post_value('pay_type'); // 用户支付类型 balance、alipay、wechat
        $money = get_post_value('money'); // 用户实际支付金额
        $type = get_post_value('type'); // 咨询类型  私人医生 院后指导
        $guidance_during = intval(get_post_value('guidance_during', 0)); // 院后指导预约时长 天数
        $private_during = get_post_value('private_during', ''); // 私人医生预约时长 abcde
        $profile = $this->getProfileData(); // 用户病历

        // 非必要参数
        //$couponId = get_post_value('coupon_id', '0'); // 使用的优惠券id
        $couponId = '0';

        // 验参
        $validateResult = validate_number($did) &&
                          validate_words($payType, ['balance', 'alipay', 'wechat']) &&
                          validate_number($money) &&
                          validate_words($type, ['院后指导', '私人医生']);
                          validate_number($couponId) &&
                          $this->checkProfileData($profile); // 验病历模板
        if (!$validateResult) {
            if (empty($did) || empty($payType) || empty_without_zero($money) || empty($type)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // 二验, 类型与时长
        if ($type == '院后指导') {
            if (empty($guidance_during)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            if (!validate_number($guidance_during)) {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
            $serviceType = 'guidance';
            $during = $guidance_during;
        } else {
            if (empty($private_during)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            if (!validate_words($private_during, ['a', 'b', 'c', 'd', 'e'])) {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
            $serviceType = 'private';
            $during = $private_during;
        }

        $useBalance = ($payType == 'balance') ? true : false;

        // 判断 是否 已有待确定预约/已有开始服务
        $consultation = new ConsultationLogic();
        $prepareResult = $consultation->prepareNewService($cid, $did, $serviceType, $money, $couponId, $useBalance, $during, $real_price);

        if ($prepareResult !== true) {
            if (in_array($prepareResult, $this->errKeys)) {
                return $this->private_result($prepareResult);
            } else {
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "未通过前置检查");
            }
        }

        $extraData = [
            'profile' => $profile,
            'couponId' => $couponId,
            'during' => $during,
            'time' => time(),
            'real_money' => $real_price,
        ];

        trans_start();

        // 新建订单信息
        $order = new OrderLogic();
        $orderData = $order->addOrder($cid, $did, $serviceType, $payType, $money, $extraData);
        if ($orderData === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "新建订单失败");
        }
        $orderId = $orderData['or_id'];

        // 处理优惠券
        if (!empty($couponId)) {
            $useConResult = $consultation->updateCoupon($couponId, $cid);
            if ($useConResult === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "优惠券处理失败");
            }
        }

        if ($useBalance) {
            // 余额支付 处理状态 返回数据
            $completeData = $order->completeOrder($orderId, $cid);
            if ($completeData === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "完成订单失败");
            }
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, array_merge($orderData, $completeData));

        } else {
            // 充值支付 返回数据
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, $orderData);
        }
    }

    public function getServiceConList()
    {
        $user = $this->checkTokenAndGetUser();
        if ($user == false) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $seId = get_post_value('se_id');
        $type = get_post_value('con_type');
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');

        $validateResult = validate_number($seId) &&
                          validate_words($type, ['视频咨询', '电话咨询']) &&
                          validate_number($page) &&
                          validate_number($num);

        if (!$validateResult) {
            if (empty($seId) || empty($type)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getServiceRecord($seId, $type, $page, $num);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    public function getAppointmentStatus()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $apId = get_post_value('ap_id');

        $validateResult = validate_number($apId);

        if (!$validateResult) {
            if (empty($seId)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getAppointStatus($cid, $apId);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    public function getServiceStatus()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $seId = get_post_value('se_id');

        $validateResult = validate_number($seId);

        if (!$validateResult) {
            if (empty($seId)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $result = $consultation->getServiceStatus($cid, $seId);

        if (in_array($result, $this->errKeys)) {
            return $this->private_result($result);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    public function getServices()
    {
        $user = $this->checkTokenAndGetUser();
        if ($user === false) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        if ($user['type'] != 'doctor') {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $did = $user['id'];

        $type = get_post_value('type');

        $validateResult = validate_words($type, ['院后指导', '私人医生']);

        if (!$validateResult) {
            if (empty($type)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $consultation = new ConsultationLogic();
        $res = $consultation->getServices($did, $type);

        if ($res === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        return $this->private_result(RESPONSE_SUCCESS, $res);
    }

    private function getProfileData()
    {
        $keys = [
            'department', 'name', 'age', 'gender', 'content',
            'blood_type',
            'is_born', 'born_time', 'born_type', 'is_allergy', 'allergy', 'smoke', 'drink',
            'prepare_pregnant_time',
            'operation_history', 'has_genetic_disease', 'genetic_disease',
            'semen_volume', 'semen_density', 'masturbation_history', 'abstinent_days',
        ];
        $data = array();
        foreach ($keys as $item) {
            if (isset($_POST[$item])) {
                $data[$item] = $_POST[$item];
            }
        }
        if (isset($data['content'])) {
            $data['content'] = safe_str($data['content']);
        }
        return $data;
    }

    private function checkProfileData($data)
    {
        $keysForValidate = array_keys($data);

        $validate = new Profile();
        $validate->scene('profile', $keysForValidate);
        $validateResult = $validate->scene('profile')->check($data);
        if (!$validateResult) {
            return false;
        }
        if (strlen($data['content']) > 200) {
            return false;
        }
        return true;
    }

    private function checkTokenAndGetCid()
    {
        $token = get_token();
        $customer = new CustomerLogic();
        $cid = $customer->valiSession($token);
        return $cid;
    }

    private function checkTokenAndGetUser()
    {
        $token = get_token();
        $customer = new CustomerLogic();
        $cid = $customer->valiSession($token);

        $userId = $cid;
        $userType = 'customer';

        if (!$cid) {
            $doctor = new DoctorLogic();
            $did = $doctor->valiSession($token);
            $userId = $did;
            $userType = 'doctor';

            if (!$did) {
                return false;
            }
        }

        return ['id' => $userId, 'type' => $userType];
    }

    private function hasEmptyValue($array)
    {
        foreach ($array as $value) {
            if ($value === '') {
                return true;
            }
        }
        return false;
    }

    private function checkImpression($impression)
    {
        $systemArray = ['非常专业认真', '态度非常好', '非常敬业', '非常清楚', '意见有很大帮助'];
        $inputArray = explode(SQL_SEPARATOR, $impression);
        foreach ($inputArray as $item) {
            if (!in_array($item, $systemArray)) {
                return false;
            }
        }
        return true;
    }

    private function checkDocGoodAt($did, $data)
    {
        $doc = new DoctorLogic();
        $goodAtArray = $doc->getDocGoodAt($did);
        $inputArray = explode(SQL_SEPARATOR, $data);
        foreach ($inputArray as $item) {
            if (!in_array($item, $goodAtArray)) {
                return false;
            }
        }
        return true;
    }

}
