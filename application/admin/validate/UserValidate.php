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

class UserValidate extends Validate
{
    protected $regex = [
        'mobile' => '1[3578]\d{9}'
    ];

    protected $rule = [
        'mobile'    => 'require|number|length:11|regex:mobile',
        'nick_name' => 'require|max:30',
        'real_name' => 'require|max:30',
        'password'  => 'require|min:6',
        'tube_stage' => 'require|in:前期准备,降调,促排,取卵,移植,验孕',
        'avatar' => 'require',
        'gender' => 'in:男,女,male,female,,',
        'birthday' => 'number',
        'age' => 'number',
        'blood_type' => 'in:O,A,B,AB,,',
        'province' => 'chsAlpha',
        'city' => 'chsAlpha',
        'oauth_type' => 'require|in:wechat,qq',
        'oauth_id' => 'require',
        'user_name' => 'require|max:30',
        'user_mobile' => 'number',
        'bank_name' => 'require|max:200',
        'bank_account' => 'require|number',
        'money' => 'require|number|gt:0',
    ];

    protected $message = [
        'mobile'            => '手机号码格式错误',
        'mobile.require'    => '手机号不能为空',
        'nick_name.require' => '昵称不能为空',
        'nick_name.max'     => '昵称长度不能超过20',
        'real_name'         => '真实姓名不符合',
        'password.require'  => '密码不能为空',
        'password.min'      => '密码过短',
        'tube_stage'        => '阶段名称不符合,必须是以下之一:前期准备,降调,促排,取卵,移植,验孕',
        'avatar' => '头像不能为空',
        'gender' => '性别设置错误',
        'birthday' => '生日时间错误',
        'age' => '生日时间错误',
        'blood_type' => '血型设置错误',
        'province' => '省份设置错误',
        'city' => '城市设置错误',
        'oauth_type' => '第三方类型必须',
        'oauth_id' => '第三方id必须',
        'user_name' => '请填写真实姓名',
        'user_mobile' => '请填写联系手机号',
        'bank_name' => '请填写银行名称',
        'bank_account' => '请填写银行账号',
        'money' => '请填写提现金额',
    ];

    protected $scene = [
        'login' => [ 'mobile','password' ],
        'tube_stage' => [ 'tube_stage' ],
        'nick_name' => [ 'nick_name' ],
        'real_name' => [ 'real_name' ],
        'avatar' => [ 'avatar' ],
        'gender' => [ 'gender' ],
        'birthday' => [ 'birthday' ],
        'age' => [ 'age' ],
        'blood_type' => [ 'blood_type' ],
        'province' => [ 'province' ],
        'city' => [ 'city' ],
        'oauth' => ['oauth_type', 'oauth_id', 'nick_name', 'avatar', 'province', 'city'],
        'withdraw' => [ 'user_name', 'user_mobile', 'bank_name', 'bank_account', 'money' ],
        'mobile' => ['mobile']
    ];

}