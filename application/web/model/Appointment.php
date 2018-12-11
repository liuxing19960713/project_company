<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/19/16
 * Time: 11:35
 */

namespace app\web\model;

use think\Model;

class Appointment extends Model
{
    protected $pk = 'ap_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

}
