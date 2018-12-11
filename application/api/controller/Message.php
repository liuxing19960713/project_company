<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\ChatRecord;
use app\common\model\Consultation;
use app\common\model\MessageUser;
use app\common\model\User;

class Message extends Base
{
    /**
     * 消息中心
     * @return \think\response\Json
     */
    public function center()
    {
        $userId = $this->getUserId();
        $whereMap = [
            'user_id' => $userId,
            'is_deleted' => '0',
        ];
        $unreadWhereMap = [ 'has_read' => '0' ];
        $orderMap = [ 'create_time' => 'desc' ];
        $fieldList = [ 'meu_id', 'content' ];

        # 咨询消息
        $whereMap['message_type'] = 'con';
        $conMessage = MessageUser::build()->field($fieldList)->where($whereMap)->order($orderMap)->find() ?: [];
        $conMessage['unread'] = MessageUser::build()->where($whereMap)->where($unreadWhereMap)->count();

        # 聊天消息
        $whereMap['message_type'] = 'chat';
        $chatMessage = MessageUser::build()->field($fieldList)->where($whereMap)->order($orderMap)->find() ?: [];
        $chatMessage['unread'] = MessageUser::build()->where($whereMap)->where($unreadWhereMap)->count();

        # 系统消息
        $whereMap['message_type'] = 'system';
        $systemMessage = MessageUser::build()->field($fieldList)->where($whereMap)->order($orderMap)->find() ?: [];
        $systemMessage['unread'] = MessageUser::build()->where($whereMap)->where($unreadWhereMap)->count();

        $this->addRenderData('con', $conMessage);
        $this->addRenderData('chat', $chatMessage);
        $this->addRenderData('system', $systemMessage);
        return $this->getRenderJson();
    }

    /**
     * 消息列表
     * @return \think\response\Json
     */
    public function index()
    {
        $messageType = $this->getParam('message_type');
        $this->checkSingle($messageType, 'message_type', 'Base.message_type');

        # 获取消息列表
        $userId = $this->getUserId();
        $whereMap = [
            'user_id' => $userId,
            'message_type' => $messageType,
            'is_deleted' => '0',
        ];
        $orderMap = [ 'create_time' => 'desc' ];
        $messageModelList = MessageUser::build()
            ->field([ 'meu_id', 'content', 'from_user_id', 'has_read', 'sub_type', 'create_time', 'event_id' ])
            ->where($whereMap)
            ->order($orderMap)
            ->page($this->pageIndex, $this->pageSize)
            ->select();

        # 聊天消息添加用户信息
        if ($messageType == 'chat') {
            $userIdList= [];
            foreach ($messageModelList as $item) {
                $userIdList[] = $item['from_user_id'];
            }
            $userModelList =  User::usersInList($userIdList, array_merge(User::userMiniField(), ['easemob_username']));
            foreach ($messageModelList as $index => $item) {
                $item['user_info'] = $userModelList[$item['from_user_id']];
            }
        }

        $this->addRenderData('messages', $messageModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 单条消息设置已读
     * @return \think\response\Json
     */
    public function read()
    {
        $messageId = $this->getParam('meu_id');
        $this->checkSingle($messageId, 'id', 'Base.id');
        $whereMap = [
            'meu_id' => $messageId,
            'user_id' => $this->getUserId()
        ];

        MessageUser::update([ 'has_read' => '1' ], $whereMap);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }

    /**
     * 全部消息设置已读
     * @return \think\response\Json
     */
    public function readAll()
    {
        $messageType = $this->getParam('message_type');
        $this->checkSingle($messageType, 'message_type', 'Base.message_type');

        $whereMap = [
            'user_id' => $this->getUserId(),
            'message_type' => $messageType,
            'has_read' => '0',
            'is_deleted' => '0',
        ];

        # 消息列表设为已读
        MessageUser::update(['has_read' => '1'], $whereMap);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }

    /**
     * 全部已读消息清空
     * @return \think\response\Json
     */
    public function clearAll()
    {
        $messageType = $this->getParam('message_type');
        $this->checkSingle($messageType, 'message_type', 'Base.message_type');

        $whereMap = [
            'user_id' => $this->getUserId(),
            'message_type' => $messageType,
            'is_deleted' => '0',
            'has_read' => '1',
        ];

        # 消息列表设为已读
        MessageUser::update(['is_deleted' => '1'], $whereMap);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }

    /**
     * 未读消息数量
     * @return \think\response\Json
     */
    public function hasNewMessage()
    {
        $userId = $this->getUserId();
        $whereMap = [
            'user_id' => $userId,
            'is_deleted' => '0',
            'has_read' => '0'
        ];
        $unreadCount = 0;
        $unreadCount += MessageUser::build()->where($whereMap)->where(['message_type' => 'con'])->count();
        $unreadCount += MessageUser::build()->where($whereMap)->where(['message_type' => 'chat'])->count();
        $unreadCount += MessageUser::build()->where($whereMap)->where(['message_type' => 'system'])->count();
        $this->addRenderData('unread', $unreadCount);
        return $this->getRenderJson();
    }

    public function uploadChatMessage()
    {
        $requestData = $this->selectParam([ 'con_id', 'msg_type', 'content', 'create_time' ]);
        // todo: check

        $consultationModel = Consultation::build()
            ->field(['con_id', 'c_id', 'd_id', 'state'])
            ->find($requestData['con_id']);
        if (empty($consultationModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('无效的咨询信息');
            return $this->getRenderJson();
        }
        if ($consultationModel['state'] != '进行中') {
            $this->setRenderCode(400);
            $this->setRenderMessage('该咨询已完成');
            return $this->getRenderJson();
        }

        ChatRecord::create([
            'c_id' => $this->getUserId(),
            'd_id' => $consultationModel['d_id'],
            'con_id' => $consultationModel['con_id'],
            'content' => $requestData['content'],
            'msg_type' => $requestData['msg_type'],
            'chat_to' => 'c2d',
            'create_time' => $requestData['create_time'],
        ]);

        $this->setRenderMessage('上传成功');

        return $this->getRenderJson();
    }

    public function downloadChatMessage()
    {
        $requestData = $this->selectParam([ 'con_id', 'd_id', 'begin_time', 'end_time' ]);
        // todo: check

        $whereMap = [
            'con_id' => $requestData['con_id'],
            'c_id' => $this->getUserId(),
            'd_id' => $requestData['d_id'],
        ];
        if (!empty($requestData['begin_time']) && !empty($requestData['end_time'])) {
            $whereMap['create_time'] = [ 'between', [ $requestData['begin_time'], $requestData['end_time'] ] ];
        }

        $chatRecordModelList = ChatRecord::build()
            ->where($whereMap)
            ->order([ 'create_time' => 'desc' ])
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        $this->addRenderData('chat', $chatRecordModelList, false);


        $whereMap = [
            'con.con_id' => $requestData['con_id'],
        ];
        $field = [
            'con.con_id as con_id', 'con.c_id as c_id', 'con.d_id as d_id', 'con.money as money',
            'cp.content as content', 'cp.department as department', 'cp.age as customer_age',
            'c.avatar as customer_avatar', 'cp.name as customer_name', 'cp.gender as customer_gender',
            'c.easemob_username as customer_easemob_username',
            'd.nick_name as doctor_name', 'd.avatar as doctor_avatar', 'd.gender as doctor_gender',
            'd.easemob_username as doctor_easemob_username'
        ];
        $consultationInfo = Consultation::build()->alias('con')
            ->field($field)->where($whereMap)
            ->join('yyb_user c', 'c.user_id =con.c_id')
            ->join('yyb_doctor d', 'd.doctor_id =con.d_id')
            ->join('yyb_consultation_profile cp', 'con.con_id = cp.con_id', 'LEFT')
            ->find();
        if (empty($consultationInfo)) {
            $consultationInfo = [];
        } else {
            if (empty($consultationInfo['money'])) {
                $consultationInfo['buy_type'] = "coupon"; //优惠券
            } else {
                $consultationInfo['buy_type'] = "cash"; //付费
            }
        }
        $this->addRenderData('extraInfo', $consultationInfo);

        return $this->getRenderJson();
    }

    public function uploadPrivateChatMessage()
    {
        $requestData = $this->selectParam([ 'user_id', 'content' ]);
        // todo: check

        $userModel = $this->getUserModel();
        $extraInfo = [
            'from_user_id' => $this->getUserId(),
            'avatar' => $userModel['avatar'],
            'gender' => $userModel['gender'],
        ];
        $extraInfo = json_encode($extraInfo, JSON_UNESCAPED_UNICODE);
        MessageUser::create([
            'user_id' => $requestData['user_id'],
            'content' => $requestData['content'],
            'message_type' => 'chat',
            'from_user_id' => $this->getUserId(),
            'extra' => $extraInfo,
        ]);
        $this->setRenderMessage('上传成功');

        return $this->getRenderJson();
    }

    public function readUserMessage()
    {
        $fromUserId = $this->getParam('from_user_id');
        $this->checkSingle($fromUserId, 'id', 'Base.id');

        $whereMap = [
            'user_id' => $this->getUserId(),
            'from_user_id' => $fromUserId,
            'message_type' => 'chat',
            'has_read' => '0',
            'is_deleted' => '0',
        ];

        # 消息列表设为已读
        MessageUser::update(['has_read' => '1'], $whereMap);

        $this->setRenderMessage('success');
        return $this->getRenderJson();
    }
}
