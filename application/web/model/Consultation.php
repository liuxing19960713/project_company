<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/21/16
 * Time: 15:33
 */

namespace app\web\model;

use think\Model;

class Consultation extends Model
{
    protected $pk = 'con_id';

    protected $insert = [
        'create_time',
    ];

    protected function setCreateTimeAttr()
    {
        return time();
    }

    /**
     * @return Consultation
     */
    public static function build()
    {
        return new self();
    }

}
