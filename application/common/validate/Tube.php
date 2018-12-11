<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Tube extends Validate
{
    protected $rule = [
        'tube_stage'    => 'require|in:0,1,2,3,4,5',
        'event_name' => 'require',
        'event_value' => 'require|boolean',
        'check_content' => 'require',
        'user_id' => 'require|number|gt:0',
        'trc_id' => 'require|number|gt:0',
    ];

    protected $message = [
        'tube_stage.require' => '阶段参数不能为空',
        'tube_stage.in'    => '阶段参数错误',
        'event_name' => '检查名不能为空',
        'event_value' => '参数错误',
        'check_content' => '不能为空',
        'user_id' => '用户id错误',
        'trc_id' => 'id错误',
    ];

    protected $scene = [
        'index' => [ 'tube_stage' ],
        'eventUpdate' => [ 'tube_stage', 'event_name', 'event_value' ],
        'checksIndex' => [ 'tube_stage', 'event_name' ],
        'checksStore' => [ 'tube_stage', 'event_name', 'check_content' ],
        'model_checksStore' => [ 'tube_stage', 'event_name', 'check_content', 'user_id' ],
        'checkUpdate' => ['trc_id', 'check_content'],
    ];
}
