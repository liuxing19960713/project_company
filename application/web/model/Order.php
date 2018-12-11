<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/16/16
 * Time: 10:40
 */

namespace app\web\model;

use think\Model;

class Order extends Model
{
    protected $pk = 'or_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}