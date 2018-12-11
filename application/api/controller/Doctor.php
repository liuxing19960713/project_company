<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\Doctor as DoctorModel;
use app\common\model\DoctorFollow;
use app\common\model\Gift;
use app\common\model\Order;
use app\common\model\Timeline;
use app\common\model\User;
use app\web\logic\DoctorLogic;
use think\Db;

class Doctor extends Base
{
    /**
     * 医生搜索
     * 从一期移过来的
     * @return \think\response\Json
     */
    public function search()
    {
        $keyword = $this->getParam('keyword', '');
        $this->checkSingle($keyword, '', 'Base.keyword');

        $doctor = new DoctorLogic();
        $res = $doctor->searchByKeyword($keyword, $this->pageIndex, $this->pageSize);

        $this->addRenderData('doctors', $res, false);
        return $this->getRenderJson();
    }

    public function index()
    {
        $requestParam = $this->selectParam([
            'province' => '', 'city' => '',
            'con_type' => '', 'sort_type' => 'desc',
        ]);

        $doctor = new DoctorLogic();
        $res = $doctor->getList($requestParam, $this->pageIndex, $this->pageSize);

        $this->addRenderData('doctors', $res, false);
        return $this->getRenderJson();
    }

    /**
     * 获取医生详情
     * 从一期移过来的
     * @return \think\response\Json
     */
    public function getDetail()
    {
        $doctorID = $this->getParam('doctor_id', '');
        $this->checkSingle($doctorID, 'id', 'Base.id');

        $doctor = new DoctorLogic();
        $res = $doctor->getDetail($doctorID);

        // 添加是否已关注
        $is_followed = DoctorFollow::isFollowed($this->getUserId(), $doctorID);
        $res['is_followed'] = $is_followed;

        if (!$res) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到');
            return $this->getRenderJson();
        }

        $this->addRenderData('doctor', $res);
        return $this->getRenderJson();
    }

    public function getComments()
    {
        $doctorID = $this->getParam('doctor_id', '');
        $this->checkSingle($doctorID, 'id', 'Base.id');

        $doctor = new DoctorLogic();
        $res = $doctor->getComments($doctorID, $this->pageIndex, $this->pageSize);

        $this->addRenderData('comments', $res, false);
        return $this->getRenderJson();
    }

    public function posts()
    {
        $doctorID = $this->getParam('doctor_id', '');
        $this->checkSingle($doctorID, 'id', 'Base.id');

        $res = DoctorLogic::getPosts($doctorID, $this->pageIndex, $this->pageSize);

        $this->addRenderData('posts', $res, false);
        return $this->getRenderJson();
    }

    public function getTimeLine()
    {
        $doctorId = $this->getParam('doctor_id');
        $timeType = $this->getParam('time_type'); // today, tomorrow

        $whereMap = [
            'd_id' => $doctorId,
        ];
        $timelineModel = Timeline::build()->where($whereMap)->find();
        if (empty($timelineModel)) {
            Timeline::create($whereMap);
            $timelineModel = Timeline::build()->where($whereMap)->find();
        }

        $s1 = array_values(customUnserialize($timelineModel['schedule']));
        if ($timeType == 'today') {
            $s2 = array_values(customUnserialize($timelineModel['today_s']));
        } else {
            $s2 = array_values(customUnserialize($timelineModel['tomorrow_s']));
        }

        $schedule = array();
        for ($i = 0; $i < count($s1); $i++) {
            if ($s1[$i] == "yes" && $s2[$i] == "yes") {
                $schedule[] = "yes";
            } else {
                $schedule[] = "no";
            }
        }

        $this->addRenderData('schedule', $schedule, false);
        return $this->getRenderJson();
    }

    public function sendGift()
    {
        $requestData = $this->selectParam([ 'pay_type', 'money', 'doctor_id', 'title', 'content' ]);
        //        $titles = ['一点心意', '白衣天使', '医德高尚', '德医双馨', '妙手仁心'];
//        $this->check(); // todo

        $user = $this->getUserModel();

        if ($requestData['pay_type'] == 'balance' && $user['money'] < $requestData['money']) {
            $this->setRenderCode(400);
            $this->setRenderMessage('余额不足');
            return $this->getRenderJson();
        }

        if ($requestData['money'] <= 0) {
            $this->setRenderCode(400);
            $this->setRenderMessage('金额错误');
            return $this->getRenderJson();
        }

        Db::startTrans();
        $userModel = $this->getUserModel();
        $extraData = [
            'title' => $requestData['title'],
            'content' => $requestData['content'],
            'username' => $userModel['nick_name'],
        ];
        $orderData = Order::addUserOrder(
            $this->getUserId(),
            $requestData['doctor_id'],
            'gift',
            $requestData['pay_type'],
            $requestData['money'],
            $extraData
        );
        if (empty($orderData)) {
            Db::rollback();
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            return $this->getRenderJson();
        }
        if ($requestData['pay_type'] == 'balance') {
            // 余额支付 处理状态 返回数据
            $completeData = Order::completeOrder($orderData['or_id'], $this->getUserId(), false);
            if (empty($completeData)) {
                Db::rollback();
                $this->setRenderCode(500);
                $this->setRenderMessage('网络异常');
                return $this->getRenderJson();
            }
            $this->addRenderData('gift', $completeData);
        }
        Db::commit();
        $this->addRenderData('order_info', $orderData);
        return $this->getRenderJson();
    }

    public function giftList()
    {
        $doctorId = $this->getParam('doctor_id');
        $this->checkSingle($doctorId, 'id', 'Base.id');

        $giftField = [ 'g_id', 'c_id', 'gift', 'title', 'content', 'create_time' ];
        $whereMap = [
            'd_id' => $doctorId,
        ];
        $orderMap = [
            'create_time' => 'desc',
        ];
        $giftModelList = Gift::build()->field($giftField)->where($whereMap)->order($orderMap)->page(1, 5)->select();

        $userIdList = [];
        foreach ($giftModelList as $item) {
            $userIdList[] = $item['c_id'];
        }

        $userModelMap = User::usersInList($userIdList);
        foreach ($giftModelList as $item) {
            $item['user_info'] = $userModelMap[$item['c_id']];
        }

        $this->addRenderData('gift_list', $giftModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 找医生
     * @return \think\response\Json
     */
//    public function index()
//    {
//        $field = [
//            'doctor_id', 'nick_name', 'avatar', 'title', 'gender', 'hospital', 'good_at',
//        ];
//        $whereMap = [
//            'is_deleted' => 'no',
//            'audit_status' => 'yes',
//        ];
//        $orderMap = [
//            'doctor_id' => 'desc'
//        ];
//        $modelList = DoctorModel::build()
//            ->field($field)
//            ->where($whereMap)
//            ->order($orderMap)
//            ->page($this->pageIndex, $this->pageSize)
//            ->select();
//        foreach ($modelList as $item) {
//            $item['good_at'] = explode('|', $item['good_at']);
//        }
//
//        $this->addRenderData('doctors', $modelList, false);
//        return $this->getRenderJson();
//    }

    /**
     * 医生详情
     * @return \think\response\Json
     */
//    public function show()
//    {
//        $id = $this->getParam('doctor_id');
//        $this->checkSingle($id, 'id', 'Base.id');
//
//        $field = [
//            'doctor_id', 'nick_name', 'avatar', 'title', 'gender', 'hospital', 'good_at',
//        ];
//        $whereMap = [
//            'doctor_id' => $id,
//            'audit_status' => 'yes',
//            'is_deleted' => 'no',
//        ];
//        $model = DoctorModel::build()->field($field)->where($whereMap)->find();
//        if (empty($model)) {
//            $this->setRenderCode(404);
//            $this->setRenderMessage('找不到了');
//            $this->addRenderData('info', "doctor not found, doctor_id: $id");
//            return $this->getRenderJson();
//        }
//
//        $this->addRenderData('doctor', $model);
//        return $this->getRenderJson();
//    }
}
