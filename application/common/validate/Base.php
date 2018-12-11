<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Base extends Validate
{
    protected $rule = [
        'page_index' => 'require|number|gt:0',
        'page_size' => 'require|number|gt:0',
        'id' => 'require|number|gt:0',
        'content' => 'require|max:300',
        'mobile' => 'require|number',
        'sms_type' => 'require|in:reg,pass,bind',
        'code' => 'require|integer|max:6',
        'password' => 'require|min:6',
        'message_type' => 'require|in:con,chat,system',
        'province' => 'chsAlpha',
        'city' => 'chsAlpha',
        'finance_type' => 'in:extend,withdrawal,invite,refund,charge,gift,private,video,phone,image,all,,',
        'money' => 'require|number|egt:1',
        'pay_type' => 'require|in:alipay,wechat',
        'nya' => 'in:no,yes,all,,',
        'ny' => 'in:no,yes',
        'tf' => 'in:no,yes,0,1,true,false',
        'id_can_null' => 'number|gt:0',
        'keyword' => 'chsDash',
    ];

    protected $message = [
        'page_index' => '页码要大于0哦',
        'page_size' => '每页数量要大于0哦',
        'id' => 'id参数不符合',
        'content.require' => '内容不能为空',
        'content.max' => '超出最大长度',
        'mobile' => '手机格式错误',
        'sms_type' => '短信类型错误',
        'code' => '验证码格式错误',
        'password.require' => '密码必须',
        'password.min'   => '密码过短',
        'message_type' => '消息类型错误',
        'province' => '省份设置错误',
        'city' => '城市设置错误',
        'finance_type' => '账单类型错误',
        'money' => '金额格式不正确',
        'pay_type' => '充值方式不正确',
        'nya' => '参数不正确',
        'ny' => '参数不正确',
        'id_can_null' => '参数不正确',
        'keyword' => '关键字不能带符号',
        'tf' => 'bool错误',
    ];

    protected $scene = [
        'page_index' => ['page_index'],
        'page_size' => ['page_size'],
        'page' => ['page_index', 'page_size'],
        'id' => ['id'],
        'password' => ['password'],
        'content' => ['content'],
        'sendSms' => ['mobile', 'sms_type'],
        'validateCode' => ['mobile', 'sms_type', 'code'],
        'register' => ['mobile', 'code', 'password', 'province', 'city'],
        'bind' => ['mobile', 'code', 'password'],
        'reset_password' => ['mobile', 'code', 'password'],
        'message_type' => ['message_type'],
        'finance_type' => ['finance_type'],
        'charge' => [ 'money', 'pay_type' ],
        'nya' => ['nya'],
        'ny' => ['ny'],
        'id_can_null' => ['id_can_null'],
        'keyword' => ['keyword'],
        'tf' => ['tf'],
    ];

    public function getEnumRule($words)
    {
        $reg = implode('|', $words);
        $rule = "/^({$reg})$/";
        return $rule;
    }
}