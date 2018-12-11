<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\CommPosts;
use app\common\model\ConsultationProfile;
use app\common\model\ConsultationReport;
use app\common\model\DoctorFollow;
use app\common\model\Finance;
use app\common\model\User;
use app\common\model\UserCoupon;
use app\common\model\UserDetail;
use app\common\model\UserFollow;
use app\common\model\Withdraw;

class Mine extends Base
{
    /**
     * 我的用户信息
     * @return \think\response\Json
     */
    public function info()
    {
        $userModel = $this->getUserModel();

        if (empty($userModel)) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "can't get user model");
            return $this->getRenderJson();
        }
        $userInfoField = [
            'user_id', 'nick_name', 'avatar', 'mobile', 'email', 'real_name',
            'gender', 'birthday', 'age', 'blood_type',
            'province', 'city',
            'money', 'fans_count', 'follow_count', 'tube_stage',
            'is_push',
            'easemob_username', 'easemob_password'
        ];
        $userInfoArray = $userModel->visible($userInfoField)->toArray();

        $this->addRenderData('user_info', $userInfoArray);
        return $this->getRenderJson();
    }

    /**
     * 我的详细信息
     * @return \think\response\Json
     */
    public function detail()
    {
        $detailArray = UserDetail::mineDetail($this->getUserId(), true);

        $this->addRenderData('detail', $detailArray);
        return $this->getRenderJson();
    }

    /**
     * 我的发帖列表a
     * @return \think\response\Json
     */
    public function posts()
    {
        $postType = trim($this->getParam('post_type', 'normal'));
        $this->checkSingle($postType, 'post_type', 'Post.post_type');

        $whereMap = [
            'user_id' => $this->getUserId(),
            'post_type' => $postType,
            'is_deleted' => 'no',
            'is_for_hospital' => '0',
        ];
        $postModelList = CommPosts::build()
            ->field(CommPosts::defaultPostModelField())
            ->where($whereMap)
            ->order(CommPosts::defaultOrderMap())
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        $this->addRenderData('posts', $postModelList, false);

        return $this->getRenderJson();
    }

    /**
     * 我的粉丝列表
     * @return \think\response\Json
     */
    public function fansList()
    {
        $fansList = UserFollow::fansList($this->getUserId(), $this->pageIndex, $this->pageSize);
        $this->addRenderData('fans', $fansList, false);
        return $this->getRenderJson();
    }

    /**
     * 我的关注列表
     * @return \think\response\Json
     */
    public function followList()
    {
        $followList = UserFollow::followList($this->getUserId(), $this->pageIndex, $this->pageSize);
        $this->addRenderData('follow', $followList, false);
        return $this->getRenderJson();
    }

    /**
     * 更新基本信息
     * @return \think\response\Json
     */
    public function infoUpdate()
    {
        $allowField = [
            'nick_name', 'avatar', 'gender', 'birthday', 'province', 'city', 'age', 'blood_type', 'real_name'
        ];
        $requestData = $this->selectParam();
        // 单个验参
        foreach ($allowField as $fieldName) {
            if (isset($requestData[$fieldName])) {
                $this->checkSingle($requestData[$fieldName], $fieldName, 'User.' . $fieldName);
                if ($fieldName == 'gender') {
                    $requestData['gender'] = ($requestData['gender'] == '男') ? 'male' : $requestData['gender'];
                    $requestData['gender'] = ($requestData['gender'] == '女') ? 'female' : $requestData['gender'];
                }
                if ($fieldName == 'birthday') {
                    $requestData['age'] = intval(date('Y')) - intval(date('Y', $requestData['birthday']));
                }
                if ($fieldName == 'avatar') {
                    $requestData['avatar'] = config('qiniu.bucketDomain') . $requestData['avatar'];
                }
            }
        }
        $this->getUserModel()->isUpdate(true)->allowField($allowField)->save($requestData);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }

    /**
     * 更新详细信息
     * @return \think\response\Json
     */
    public function detailUpdate()
    {
        $requestData = $this->selectParam();

        $detailModel = UserDetail::mineDetail($this->getUserId());
        $detailModel->isUpdate(true)->allowField(true)->save($requestData);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }

    /**
     * 更新用户阶段
     * @return \think\response\Json
     */
    public function stageUpdate()
    {
        $stage = $this->getParam('tube_stage');
        $this->checkSingle($stage, 'tube_stage', 'User.tube_stage');

        $userModel = $this->getUserModel();
        $userModel->isUpdate(true)->save([
            'tube_stage' => $stage
        ]);

        $this->setRenderMessage('success');
        $this->addRenderData('tube_stage', $stage);
        return $this->getRenderJson();
    }

    /**
     * 设置是否接收推送
     * @return \think\response\Json
     */
    public function setPush()
    {
        $isPush = $this->getParam('is_push');
        $this->checkSingle($isPush, 'tf', 'Base.tf');

        $updateData = [];
        if ($isPush && $isPush != 'no' && $isPush != 'false') {
            $updateData['is_push'] = 'yes';
        } else {
            $updateData['is_push'] = 'no';
        }

        $this->getUserModel()->isUpdate(true)->save($updateData);

        $this->setRenderMessage('修改成功');
        return $this->getRenderJson();
    }

    /**
     * 我的医生列表
     * @return \think\response\Json
     */
    public function doctors()
    {
        $doctorList = DoctorFollow::mineDoctorList($this->getUserId(), $this->pageIndex, $this->pageSize);
        $this->addRenderData('doctor', $doctorList, false);
        return $this->getRenderJson();
    }

    /**
     * 我的账单
     * @return \think\response\Json
     */
    public function finance()
    {
        $financeType = $this->getParam('type');
        $this->checkSingle($financeType, 'finance_type', 'Base.finance_type'); // todo

        $financeModelList = Finance::selectUserFinance(
            $this->getUserId(),
            $financeType,
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('finance', $financeModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 我的优惠券
     */
    public function coupon()
    {
        $requestData = $this->selectParam(['used', 'doctor_id']);
        $this->checkSingle($requestData['doctor_id'], 'id_can_null', 'Base.id_can_null');
        $this->checkSingle($requestData['used'], 'nya', 'Base.nya');

        $userCoupon = UserCoupon::getCouponList(
            $this->getUserId(),
            $requestData['used'],
            $requestData['doctor_id'],
            $this->pageIndex,
            $this->pageSize
        );

        $this->addRenderData('coupon', $userCoupon, false);
        return $this->getRenderJson();
    }

    /**
     * 问诊报告列表
     * @return \think\response\Json
     */
    public function reportList()
    {
        $reportModelList = ConsultationReport::myReportList($this->getUserId(), $this->pageIndex, $this->pageSize);
        $this->addRenderData('report', $reportModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 问诊报告详情
     * @return \think\response\Json
     */
    public function reportInfo()
    {
        $reportId = $this->getParam('cr_id');
        $this->checkSingle($reportId, 'id', 'Base.id');

        $report = ConsultationReport::myReportDetail($this->getUserId(), $reportId);

        $this->addRenderData('report_info', $report);
        return $this->getRenderJson();
    }

    public function withdrawList()
    {
        $userId = $this->getUserId();

        $field = [
            'wd_id', 'money', 'status', 'bank_name', 'bank_account', 'user_name', 'user_mobile', 'reason',
            'audit_time', 'create_time'
        ];
        $withdrawModelList = Withdraw::build()
            ->field($field)
            ->where([ 'user_id' => $userId, 'user_type' => 'customer' ])
            ->order(['create_time' => 'desc'])
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        $this->addRenderData('withdraw_list', $withdrawModelList, false);
        return $this->getRenderJson();
    }

    public function withdrawInfo()
    {
        $withdrawId = $this->getParam('wd_id');
        $this->checkSingle($withdrawId, 'id', 'Base.id');

        $field = [
            'wd_id', 'money', 'status', 'bank_name', 'bank_account', 'user_name', 'user_mobile', 'reason',
            'audit_time', 'create_time'
        ];
        $withdrawModel = Withdraw::build()
            ->field($field)
            ->where([ 'wd_id' => $withdrawId, 'user_type' => 'customer', 'user_id' => $this->getUserId() ])
            ->find();

        if (empty($withdrawModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('无效的提现记录id');
            $this->addRenderData('info', "can't get withdraw model by id: {$withdrawId}");
            return $this->getRenderJson();
        }

        $this->addRenderData('withdraw_info', $withdrawModel);
        return $this->getRenderJson();
    }

    public function addWithdraw()
    {
        $requestData = $this->selectParam([ 'user_name', 'user_mobile', 'bank_name', 'bank_account', 'money' ]);
        $this->check($requestData, 'User.withdraw');

        if (($requestData['money'] % 100 != 0) || (is_float($requestData['money']))) {
            $this->setRenderCode(402);
            $this->setRenderMessage('金额需要为整百');
            return $this->getRenderJson();
        }

        // todo: 检查余额 扣钱
        $userModel = $this->getUserModel();
        if ($userModel['money'] < $requestData['money']) {
            $this->setRenderCode(400);
            $this->setRenderMessage('余额不足');
            return $this->getRenderJson();
        }

        $withdrawData = array_merge($requestData, [
            'user_id' => $this->getUserId(),
            'user_type' => 'customer',
            'user_mobile' => $requestData['user_mobile'] ?: '',
        ]);
        Withdraw::create($withdrawData);
        User::build()->where(['user_id' => $this->getUserId()])->dec('money', $requestData['money'])->update();

        $this->setRenderMessage('已申请提现');

        return $this->getRenderJson();
    }
}
