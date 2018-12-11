<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\Event as EventModel;

class Event extends Base
{
    /**
     * 活动专区列表
     * @return \think\response\Json
     */
    public function index()
    {
        $field = [
            'event_id', 'doctor_id', 'doctor_name', 'doctor_avatar', 'doctor_title', 'doctor_info',
            'title', 'event_status', 'join_count', 'group_id'
        ];
        $whereMap = [
            'is_deleted' => 'no',
        ];
        $orderMap = [
            'event_id' => 'desc'
        ];
        $modelList = EventModel::build()
            ->field($field)
            ->where($whereMap)
            ->order($orderMap)
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        foreach ($modelList as $item) {
            $userInfo = [
                'doctor_id' => $item['doctor_id'],
                'nick_name' => $item['doctor_name'],
                'avatar' => $item['doctor_avatar'],
            ];
            $item['user_info'] = $userInfo;
        }

        $this->addRenderData('event_list', $modelList, false);
        return $this->getRenderJson();
    }

    /**
     * 活动专区详情
     * @return \think\response\Json
     */
    public function show()
    {
        $id = $this->getParam('event_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'event_id', 'doctor_id', 'doctor_name', 'doctor_avatar', 'doctor_title', 'doctor_info',
            'title', 'video_url', 'event_status', 'join_count', 'start_time', 'end_time', 'group_id'
        ];
        $whereMap = [
            'event_id' => $id,
            'is_deleted' => 'no',
        ];
        $model = EventModel::build()->field($field)->where($whereMap)->find();
        if (empty($model)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "event not found, event_id: $id");
            return $this->getRenderJson();
        }

        $userInfo = [
            'doctor_id' => $model['doctor_id'],
            'nick_name' => $model['doctor_name'],
            'avatar' => $model['doctor_avatar'],
        ];
        $model['user_info'] = $userInfo;

        # 数量加1
        EventModel::build()->where($whereMap)->inc('join_count')->update();

        $this->addRenderData('event', $model);

        return $this->getRenderJson();
    }
}
