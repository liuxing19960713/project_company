<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/25/16
 * Time: 16:44
 */

namespace app\index\model;

use think\Model;

class Feedback extends Model
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
