<?php

namespace app\api\controller;

use app\common\model\CommPosts;
use app\common\model\Tips as TipsModel;

class Tips extends Base
{
    /**
     * 优孕攻略列表
     * @return \think\response\Json
     */
    public function index()
    {
        $field = [
            'tip_id', 'title', 'sub_title', 'img_url', 'create_time'
        ];
        $whereMap = [
            'is_deleted' => 'no',
        ];
        $orderMap = [
            'create_time' => 'desc'
        ];
        $modelList = TipsModel::build()
            ->field($field)
            ->where($whereMap)
            ->order($orderMap)
            ->page($this->pageIndex, $this->pageSize)
            ->select();

        $this->addRenderData('tips_list', $modelList, false);
        return $this->getRenderJson();
    }

    /**
     * 优孕攻略详情
     * @return \think\response\Json
     */
    public function show()
    {
        $id = $this->getParam('tip_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'tip_id', 'title', 'sub_title', 'img_url', 'content', 'create_time'
        ];
        $whereMap = [
            'tip_id' => $id,
            'is_deleted' => 'no',
        ];
        $model = TipsModel::build()->field($field)->where($whereMap)->find();
        if (empty($model)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "tips not found, tip_id: $id");
            return $this->getRenderJson();
        }

        // 取出3个相关案例
        $postModelList = TipsModel::posts($id, [], 1, 3);
        $model['posts'] = $postModelList;

        // 取出3个金牌专家
        $doctorModelList = TipsModel::doctors($id, [], 1, 3);
        $model['doctors'] = $doctorModelList;

        $this->addRenderData('tip', $model);

        return $this->getRenderJson();
    }

    /**
     * 更多相关案例列表
     * @return \think\response\Json
     */
    public function cases()
    {
        $id = $this->getParam('tip_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'tip_id'
        ];
        $whereMap = [
            'tip_id' => $id,
            'is_deleted' => 'no',
        ];
        $model = TipsModel::build()->field($field)->where($whereMap)->find();
        if (empty($model)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "tips not found, tip_id: $id");
            return $this->getRenderJson();
        }

        $postModelList = TipsModel::posts($id, [], $this->pageIndex, $this->pageSize);
        $this->addRenderData('posts', $postModelList, false);

        return $this->getRenderJson();
    }

    /**
     * 更多金牌专家列表
     * @return \think\response\Json
     */
    public function doctors()
    {
        $id = $this->getParam('tip_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'tip_id'
        ];
        $whereMap = [
            'tip_id' => $id,
            'is_deleted' => 'no',
        ];
        $model = TipsModel::build()->field($field)->where($whereMap)->find();
        if (empty($model)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "tips not found, tip_id: $id");
            return $this->getRenderJson();
        }

        $postModelList = TipsModel::doctors($id, [], $this->pageIndex, $this->pageSize);
        $this->addRenderData('doctors', $postModelList, false);

        return $this->getRenderJson();
    }
}
