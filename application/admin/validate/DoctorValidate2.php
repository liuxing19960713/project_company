<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class DoctorValidate extends Validate
{
    protected $rule = [
        ['hospital', 'require', '医院不能为空'],
        ['department1', 'require', '科室不能为空'],
        ['department2', 'require', '科室2不能为空'],
        ['is_default', 'require', '请选择状态'],
        ['is_audited', 'require', '请选择审核']
    ];

}