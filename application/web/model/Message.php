<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/17/16
 * Time: 15:42
 */

namespace app\web\model;

use think\Model;

class Message extends Model
{
    protected $pk = 'me_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

}
