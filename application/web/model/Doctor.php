<?php

/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/14/16
 * Time: 15:09
 */

namespace app\web\model;

use think\Model;

class Doctor extends Model
{
    protected $pk = 'doctor_id';

    protected function scopeAudit($query)
    {
        $query->where('audit_status', 'yes');
    }

    public function getGenderAttr($value)
    {
        $genderArray = [
            'female' => '女',
            'male' => '男',
            '' => '',
        ];
        return $genderArray[$value];
    }

    /**
     * 处理推荐热度统计
     * @param $value
     *
     * @return array
     */
    public function getStatisticGoodAtAttr($value)
    {
        $resultArray = [];
        if (!empty($value)) {
            foreach (explode(SQL_SEPARATOR, $value) as $content) {
                $k = explode(':', $content)[0];
                $v = explode(':', $content)[1];
                $resultArray[$k] = $v;
            }
            arsort($resultArray);
        }

        return array_keys($resultArray);
    }

    public function getStatisticImpressionAttr($value)
    {
        $resultArray = [];
        if (!empty($value)) {
            foreach (explode(SQL_SEPARATOR, $value) as $content) {
                $k = explode(':', $content)[0];
                $v = explode(':', $content)[1];
                $resultArray[$k] = $v;
            }
            arsort($resultArray);
        }

        return array_keys($resultArray);
    }

    public function getGoodAtAttr($value)
    {
        return explode(SQL_SEPARATOR, $value);
    }

}
