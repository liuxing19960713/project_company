<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/16/16
 * Time: 14:30
 */

namespace app\web\model;

use think\Model;

class CustomerCoupon extends Model
{
    protected $pk = 'cc_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}
