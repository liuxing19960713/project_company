<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/15/16
 * Time: 10:41
 */

namespace app\web\model;

use think\Model;

class Customer extends Model
{
    protected $pk = 'c_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

}
