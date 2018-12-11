<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/14/16
 * Time: 17:17
 */

namespace app\index\validate;

use think\Validate;

class Vali extends Validate
{
    protected $rule = [
        'nick_name' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u',
        'email' => 'email',
        'gender' => ['regex' => '/^(男|女)$/'],
        'birthday' => 'number',
        'age' => 'number',
        'blood_type' => ['regex' => '/^(O|A|B|AB)$/'],
        'marriage' => ['regex' => '/^(未婚|已婚)$/'],
        'career' => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',
        'avatar' => ['regex' => '/^[a-zA-Z0-9_:\/\.-]*$/'],
    ];

}
