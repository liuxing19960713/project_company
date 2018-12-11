<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\model;

use think\Model;

class TubeRecord extends Model
{
    protected $pk = 'tr_id';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = true;

    public function selectAllStage($userId)
    {
        $list = $this->field(['tube_stage', 'tube_stage_value'])->where(['user_id' => $userId])->select();
        foreach ($list as $item) {
            $item['tube_stage_text'] = $item->tube_stage_text;
        }
        return $list;
    }

    public function getTubeStageTextAttr($value, $data)
    {
        $textArray = [
            '0' => '前期准备',
            '1' => '降调',
            '2' => '促排',
            '3' => '取卵',
            '4' => '移植',
            '5' => '验孕',
        ];
        return $textArray[$data['tube_stage']];
    }

    /**
     * @return TubeRecord
     */
    public static function build()
    {
        return new self();
    }

    public static function whereCount($dataMap = [])
    {
        return static::build()->where($dataMap)->count();
    }

    public static function fixInitStage()
    {
        $allUserModelList = User::all();
        $dataList = [];

        $stageEventsTemplateList = [];
        foreach (range(0, 5) as $key => $item) {
            $stageEventsTemplateList[$key . ""] = self::getStageEventsTemplate($item);
        }
        foreach ($allUserModelList as $user) {
            foreach ($stageEventsTemplateList as $key => $template) {
                $dataList[] = [
                    'user_id' => $user['user_id'],
                    'tube_stage' => $key . "",
                    'stage_events' => json_encode($template, JSON_UNESCAPED_UNICODE),
                ];
            }
        }
        self::insertAll($dataList);
    }

    public static function initStage($userId)
    {
        foreach (range(0, 5) as $item) {
            $template = self::getStageEventsTemplate($item);
            $t = self::create([
                'user_id' => $userId,
                'tube_stage' => $item,
                'stage_events' => json_encode($template, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public static function getStageEventsTemplate($tubeStage)
    {
        $templateArray = [
            '0' => [
                [
                    'name' => '性激素检查',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '不孕抗体检查',
                    'index' => 1,
                    'value' => 0,
                ],
                [
                    'name' => '窦卵泡监测',
                    'index' => 2,
                    'value' => 0,
                ],
                [
                    'name' => '阴道分泌物',
                    'index' => 3,
                    'value' => 0,
                ],
                [
                    'name' => '宫颈细胞学',
                    'index' => 4,
                    'value' => 0,
                ],
                [
                    'name' => '心电图',
                    'index' => 5,
                    'value' => 0,
                ],
                [
                    'name' => '凝血检查',
                    'index' => 6,
                    'value' => 0,
                ],
                [
                    'name' => '精液检查',
                    'index' => 7,
                    'value' => 0,
                ],
                [
                    'name' => '染色体',
                    'index' => 8,
                    'value' => 0,
                ],
                [
                    'name' => '血常规',
                    'index' => 9,
                    'value' => 0,
                ],
                [
                    'name' => '血生化',
                    'index' => 10,
                    'value' => 0,
                ],
                [
                    'name' => '血型',
                    'index' => 11,
                    'value' => 0,
                ],
                [
                    'name' => '肝肾功能',
                    'index' => 12,
                    'value' => 0,
                ],
                [
                    'name' => '艾滋',
                    'index' => 13,
                    'value' => 0,
                ],
                [
                    'name' => '梅毒',
                    'index' => 14,
                    'value' => 0,
                ],
                [
                    'name' => '甲功三项',
                    'index' => 15,
                    'value' => 0,
                ],
                [
                    'name' => '地中海贫血基因筛查',
                    'index' => 16,
                    'value' => 0,
                ],
                [
                    'name' => 'AMH',
                    'index' => 17,
                    'value' => 0,
                ],
                [
                    'name' => '病毒八项',
                    'index' => 18,
                    'value' => 0,
                ],
                [
                    'name' => '性病三项',
                    'index' => 19,
                    'value' => 0,
                ],
                [
                    'name' => '其他',
                    'index' => 20,
                    'value' => 0,
                ],
            ],
            '1' => [
                [
                    'name' => '降调用药',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '降调方案',
                    'index' => 1,
                    'value' => 0,
                ],
                [
                    'name' => '性激素检查',
                    'index' => 2,
                    'value' => 0,
                ],
                [
                    'name' => 'B超检测',
                    'index' => 3,
                    'value' => 0,
                ],
                [
                    'name' => '其他',
                    'index' => 4,
                    'value' => 0,
                ],
            ],
            '2' => [
                [
                    'name' => '促排用药',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '促排方案',
                    'index' => 1,
                    'value' => 0,
                ],
                [
                    'name' => '性激素检查',
                    'index' => 2,
                    'value' => 0,
                ],
                [
                    'name' => 'B超检测',
                    'index' => 3,
                    'value' => 0,
                ],
                [
                    'name' => '其他',
                    'index' => 4,
                    'value' => 0,
                ],
            ],
            '3' => [
                [
                    'name' => '取卵取精',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '其他',
                    'index' => 1,
                    'value' => 0,
                ],
            ],
            '4' => [
                [
                    'name' => '移植',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '黄体支持',
                    'index' => 1,
                    'value' => 0,
                ],
                [
                    'name' => '妊娠结局',
                    'index' => 2,
                    'value' => 0,
                ],
                [
                    'name' => '其他',
                    'index' => 3,
                    'value' => 0,
                ],
            ],
            '5' => [
                [
                    'name' => '移植14天后抽血查hcg',
                    'index' => 0,
                    'value' => 0,
                ],
                [
                    'name' => '移植28天后B超检测',
                    'index' => 1,
                    'value' => 0,
                ],
            ]
        ];
        return $templateArray[$tubeStage];
    }
}
