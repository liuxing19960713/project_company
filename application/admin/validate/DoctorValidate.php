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
        ['nick_name', 'require', '姓名不能为空'],
        ['province', 'require', '地区不能为空'],
        ['city', 'require', '地区不能为空']
    ];

}