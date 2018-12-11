<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Report extends Validate
{
    protected $rule = [
        'report_type' => 'require|in:post,comment',
        'report_type_id' => 'require|number|gt:0',
        'reason' => 'require',
        'user_id' => 'require|number|gt:0',
    ];

    protected $message = [
        'report_type' => '请选择举报类型',
        'report_type_id' => 'id参数不符合',
        'reason' => '请输入举报原因',
        'user_id' => '用户id参数不符合',
    ];

    protected $scene = [
        'store' => ['report_type', 'report_type_id', 'reason'],
        'model_store' => ['report_type', 'report_type_id', 'reason', 'user_id'],
    ];
}
