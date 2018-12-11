<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/16/16
 * Time: 21:36
 */

namespace app\web\model;

use think\Model;

class ExConsultation extends Model
{
    protected $pk = 'exc_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }
}