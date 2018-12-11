<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\TubeRecord as TubeRecordModel;
use app\common\model\TubeRecordChecks as CheckModel;

class TubeRecord extends Base
{
    /**
     * 查看试管记录进度 （圈圈勾勾：圈圈百分比和底下的勾勾）
     *
     * @return \think\response\Json
     */
    public function index()
    {
        # 获取参数、验证参数
        $tubeStage = $this->getParam('tube_stage', '0');
        $this->checkSingle($tubeStage, 'tube_stage', 'Tube.index');
        $userId = $this->getUserId();

        # 查询用户的6个阶段记录
        $tubeRecordList = TubeRecordModel::build()->selectAllStage($userId);
        if (empty($tubeRecordList)) {
            // 记录为空 创建初始记录，再重新查询
            TubeRecordModel::initStage($userId);
            $tubeRecordList = TubeRecordModel::build()->selectAllStage($userId);
        }

        # 直接获取数值
        $this->addRenderData('stages_list', $tubeRecordList, false);

        # 再次查询该阶段具体项目记录
        $whereMap = [
            'user_id' => $userId,
            'tube_stage' => $tubeStage,
        ];
        $events = TubeRecordModel::build()->field('stage_events')->where($whereMap)->find();
        $eventsArray = [];
        if (!empty($events)) {
            $eventsArray = json_decode($events['stage_events'], true);
        }
        $this->addRenderData('stage_events', $eventsArray, false);

        return $this->getRenderJson();
    }

    /**
     * 修改记录完成情况 （更新勾勾）
     * @return \think\response\Json
     */
    public function eventUpdate()
    {
        # 获取参数、验证参数
        $requestData = $this->selectParam(['tube_stage', 'event_name', 'event_value']);
        $this->check($requestData, 'Tube.eventUpdate');

        # 查询具体记录
        $whereMap = [
            'user_id' => $this->getUserId(),
            'tube_stage' => $requestData['tube_stage'],
        ];
        $recordModel = TubeRecordModel::build()->where($whereMap)->find();

        # 计算完成度百分比、并修改记录
        $eventsArray = json_decode($recordModel['stage_events'], true);
        $count = 0;
        foreach ($eventsArray as &$item) {
            if ($item['name'] == $requestData['event_name']) {
                $item['value'] = $requestData['event_value'] ? 1 : 0;
            }
            if ($item['value']) {
                $count++;
            }
        }
        $recordModel['tube_stage_value'] = ceil($count / count($eventsArray) * 100);
        $recordModel['stage_events'] = json_encode($eventsArray, JSON_UNESCAPED_UNICODE);
        $result = $recordModel->save();

        # 返回
        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
        }

        $this->addRenderData('tube_stage', $recordModel['tube_stage']);
        $this->addRenderData('tube_stage_value', $recordModel['tube_stage_value']);
        return $this->getRenderJson();
    }

    /**
     * 查看检查记录列表 （条条列表）
     * @return \think\response\Json
     */
    public function checksIndex()
    {
        # 获取参数、验证参数
        $requestData = $this->selectParam(['tube_stage', 'event_name']);
        $this->check($requestData, 'Tube.checksIndex');
        $userId = $this->getUserId();

        $field = [
            'trc_id',
            'check_content',
            'create_time'
        ];

        $whereMap = [
            'tube_stage' => $requestData['tube_stage'],
            'event_name' => $requestData['event_name'],
            'user_id' => $userId,
            'is_deleted' => 'no',
        ];

        # 查询记录列表
        $checksList = CheckModel::build()->field($field)
            ->where($whereMap)
            ->order(['create_time' => 'desc'])
            ->select();

        # 直接返回
        $this->addRenderData('checks_list', $checksList, false);

        return $this->getRenderJson();
    }

    /**
     * 添加检查记录 （添加条条）
     * @return \think\response\Json
     */
    public function checkStore()
    {
        # 获取参数、验证参数
        $requestData = $this->selectParam(['tube_stage', 'event_name', 'check_content']);
        $this->check($requestData, 'Tube.checksStore');
        $userId = $this->getUserId();

        # 保存数据
        $requestData['user_id'] = $userId;
        $checkModel = new CheckModel();
        $result = $checkModel->store($requestData);

        // todo: 要不要把勾勾弄成完成

        # 处理错误或返回
        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail: " . $checkModel->getError());
            return $this->getRenderJson();
        }

        // todo: 返回content的话会不会太长
        $this->addRenderData('check', $checkModel);

        return $this->getRenderJson();
    }

    /**
     * 更新记录 （更新条条）
     * @return \think\response\Json
     */
    public function checkUpdate()
    {
        # 获取参数、验证参数
        $id = $this->getParam('trc_id');
        $checkContent = $this->getParam('check_content');
        $this->check($this->selectParam(['trc_id', 'check_content']), 'Tube.checkUpdate');

        # 查看记录是否存在
        $checkModel = CheckModel::get($id);
        if (empty($checkModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "trc_id get fail: " . $id);
            return $this->getRenderJson();
        }

        # 修改字段
        $result = $checkModel->isUpdate(true)->save(['check_content' => $checkContent]);

        # 处理错误或返回
        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "delete fail: " . $checkModel->getError());
            return $this->getRenderJson();
        }

        return $this->getRenderJson();
    }

    /**
     * 删除检查记录 （删除条条）
     * @return \think\response\Json
     */
    public function checkDestroy()
    {
        # 获取记录id
        $id = $this->getParam('trc_id');
        $this->checkSingle($id, 'id', 'Base.id');

        # 查看记录是否存在
        $whereMap = [
            'trc_id' => $id,
            'user_id' => $this->getUserId()
        ];
        $checkModel = CheckModel::build()->field(['trc_id', 'user_id', 'is_deleted'])->where($whereMap)->find();
        if (empty($checkModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "trc_id get fail: " . $id);
            return $this->getRenderJson();
        }

        # 修改删除字段
        $result = $checkModel->isUpdate(true)->save(['is_deleted' => 'yes']);

        # 处理错误或返回
        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "delete fail: " . $checkModel->getError());
            return $this->getRenderJson();
        }

        return $this->getRenderJson();
    }

    /**
     * 查看某条检查记录（用不上）
     */
    public function checkShow()
    {
    }
}
