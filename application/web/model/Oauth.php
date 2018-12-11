<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/19/16
 * Time: 11:03
 */

namespace app\web\model;

use think\Model;

class Oauth extends Model
{
    protected $pk = 'id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}
