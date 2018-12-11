<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\Doctor;
use app\common\model\DoctorFollow;
use app\common\model\Feedback;
use app\common\model\MessageUser;
use app\common\model\Order;
use app\common\model\UserFollow;
use app\common\model\User;
use app\common\tools\Easemob;

class Action extends Base
{
    /**
     * 关注用户
     * @return \think\response\Json
     */
    public function followUser()
    {
        $followUserId = $this->getParam('user_id');
        $this->checkSingle($followUserId, 'id', 'Base.id');

        if ($followUserId == $this->getUserId()) {
            $this->setRenderCode(400);
            $this->setRenderMessage('不能关注自己哦');
            return $this->getRenderJson();
        }

        if (empty(User::get($followUserId))) {
            $this->setRenderCode(400);
            $this->setRenderMessage('该用户暂时无法关注');
            return $this->getRenderJson();
        }

        UserFollow::followUser($this->getUserId(), $followUserId);

        $this->setRenderMessage('关注成功');
        return $this->getRenderJson();
    }

    /**
     * 取消关注用户
     * @return \think\response\Json
     */
    public function unfollowUser()
    {
        $followUserId = $this->getParam('user_id');
        $this->checkSingle($followUserId, 'id', 'Base.id');

        $dataMap = [
            'from_user_id' => $this->getUserId(),
            'to_user_id' => $followUserId
        ];
        if (!empty(UserFollow::get($dataMap))) {
            UserFollow::destroy($dataMap);
            User::build()->where([ 'user_id' => $this->getUserId() ])->dec('follow_count')->update();
            User::build()->where([ 'user_id' => $followUserId ])->dec('fans_count')->update();
        }

        $this->setRenderMessage('已取消关注');
        return $this->getRenderJson();
    }

    /**
     * 关注医生
     * @return \think\response\Json
     */
    public function followDoctor()
    {
        $doctorId = $this->getParam('doctor_id');
        $this->checkSingle($doctorId, 'id', 'Base.id');

        if (empty(Doctor::get($doctorId))) {
            // todo: 条件应该还有别的 比如是否已验证
            $this->setRenderCode(400);
            $this->setRenderMessage('该医生暂时无法关注');
            return $this->getRenderJson();
        }

        // todo: push message
        $dataMap = [
            'user_id' => $this->getUserId(),
            'doctor_id' => $doctorId
        ];
        if (empty(DoctorFollow::get($dataMap))) {
            DoctorFollow::create($dataMap);
            // todo:
            Doctor::build()->where([ 'doctor_id' => $doctorId ])->inc('follower_count')->update();
        }

        $this->setRenderMessage('关注成功');
        return $this->getRenderJson();
    }

    /**
     * 取消关注医生
     * @return \think\response\Json
     */
    public function unfollowDoctor()
    {
        $doctorId = $this->getParam('doctor_id');
        $this->checkSingle($doctorId, 'id', 'Base.id');

        $dataMap = [
            'user_id' => $this->getUserId(),
            'doctor_id' => $doctorId
        ];
        if (!empty(DoctorFollow::get($dataMap))) {
            DoctorFollow::destroy($dataMap);
            // todo:
            Doctor::build()->where([ 'doctor_id' => $doctorId ])->dec('follower_count')->update();
        }

        $this->setRenderMessage('已取消关注');
        return $this->getRenderJson();
    }

    /**
     * 用户充值下单
     * @return \think\response\Json
     */
    public function charge()
    {
        $requestData = $this->selectParam(['money', 'pay_type']);
//        $this->check($requestData, 'Base.charge');

        $order = Order::addUserOrder($this->getUserId(), null, 'charge', $requestData['pay_type'], $requestData['money']);

        $this->addRenderData('order', $order);
        return $this->getRenderJson();
    }

    /**
     * 添加反馈信息
     * @return \think\response\Json
     */
    public function feedback()
    {
        $content = $this->getParam('content', '');
        $mobile = $this->getParam('mobile', '');
        $this->checkSingle($content, 'content', 'Base.content');

        Feedback::create([
            'member_id' => $this->getUserId(),
            'member_type' => 'customer',
            'content' =>$content,
            'mobile' => $mobile,
        ]);

        $this->setRenderMessage('感谢您的反馈');

        return $this->getRenderJson();
    }
}
