<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/21/16
 * Time: 16:21
 */

namespace app\web\model;

use think\Model;

class Gift extends Model
{
    protected $pk = "g_id";

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}
