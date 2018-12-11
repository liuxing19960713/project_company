<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Cons extends Validate
{
    protected $rule = [
        'doctor_id' => 'require|number|gt:0',
        'money' => 'require|number|egt:0',
        'cp_id' => 'require|number|gt:0',
        'coupon_id' => 'number|gt:0',
        'ex_con_id'=> 'number|gt:0',
        'pay_type' => 'require|in:balance,alipay,wechat'
    ];

    protected $message = [
        'doctor_id' => '医生id为空',
        'money' => '请输入金额',
        'cp_id' => '病历id为空',
        'coupon_id' => '优惠券id格式错误',
        'ex_con_id' => '转诊id格式错误',
        'pay_type' => '请选择支付方式',
    ];

    protected $scene = [
        'add_image_con' => [ 'doctor_id', 'money', 'cp_id', 'coupon_id', 'ex_con_id', 'pay_type' ],
    ];
}
