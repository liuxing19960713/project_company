<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/20/16
 * Time: 22:14
 */

namespace app\web\model;

use think\Model;

class Refund extends Model
{
    protected $pk = 'rf_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

}
