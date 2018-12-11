<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/16/16
 * Time: 19:29
 */

namespace app\index\validate;

use think\Validate;

class Profile extends Validate
{
    protected $rule = [
        // 必须
        'department' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u',
        'name' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u',
        'age' => 'number',
        'gender' => ['regex' => '/^(男|女)$/'],
        'content' => 'require',
        // 非必须
        'blood_type' => ['regex' => '/^(O|A|B|AB|)$/'],
        'is_born' => ['regex' => '/^(no|yes|)$/'],
        'born_time' => '/^[0-9-]*$/',
        'born_type' => ['regex' => '/^(顺产|剖腹产|)$/'],
        'is_allergy' => ['regex' => '/^(no|yes|)$/'],
        'allergy' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]*$/u',
        'smoke' => ['regex' => '/^(no|yes|)$/'],
        'drink' => ['regex' => '/^(no|yes|)$/'],
        'prepare_pregnant_time' => '/^[0-9]*$/',
        'operation_history' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]*$/u',
        'has_genetic_disease' => ['regex' => '/^(no|yes|)$/'],
        'genetic_disease' => '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]*$/u',
        'semen_volume' => ['regex' => '/^(正常|少|)$/'],
        'semen_density' => ['regex' => '/^(正常|稀少|)$/'],
        'masturbation_history' => ['regex' => '/^(no|yes|)$/'],
        'abstinent_days' => '/^[0-9]*$/',
    ];

}
