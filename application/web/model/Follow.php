<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/15/16
 * Time: 14:14
 */

namespace app\web\model;

use think\Model;

class Follow extends Model
{
    protected $pk = 'f_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

}
