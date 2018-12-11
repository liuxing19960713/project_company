<?php
namespace app\doctor\model;

use think\Model;

class Validate
{
    private $param_define
        = array(

            // 用户
            'user_id', // 用户id
            'token', // 用户token

        );

    /**
     * 验证传入参数
     * @array $vali_field 验证数组 数组格式为 $vali_field[] = array('可用参数名','接口传入参数名', 是否必填true/false);
     * 例子：
     * $vali_field[] = array('user_id',                'uid', true);
     * $vali_field[] = array('shop_id',                'sid', true);
     * @array $param 接口传入的get/post参数
     * @return array
     * {
     * '0' : '成功',
     * '1' : '缺少必填参数',
     * '2' : '参数格式错误',
     * }
     */
    public function vali_param($vali_field = array(), $param = array())
    {
        $return = array('errcode' => 0, 'errmsg' => '');

        // 验证必填参数是否缺失
        $err_flag = false;
        foreach ($vali_field as $item) {
            if ($item[2] == true) {
                if (!isset($param[$item['1']])) {
                    $err_flag = true;
                }
            }
        }
        if ($err_flag) {
            $return = array('errcode' => 1, 'errmsg' => '缺少必填参数');

            return $return;
        }

        // 验证参数格式是否正确
        $err_flag = false; // 查找到存在错误标志
        $err_item_name = ''; // 查找到的错误项
        foreach ($vali_field as $item) {
            if ($item[2] == false && !isset($param[$item['1']])) { // 可选参数不填时忽略
                continue;
            }

            $vali_function_name = vali . '_' . $item[0];

            $vali_result = $this->$vali_function_name($param[$item[1]]);
            if (!$vali_result) {
                $err_flag = true;
                $err_item_name = $item[0];
                break;
            }
        }
        if ($err_flag) {
            $return = array('errcode' => 2, 'errmsg' => $err_item_name . '参数格式错误');

            return $return;
        }

        return $return;
    }

    /**
     * 验证user_id 必须为数字
     *
     * @param $vali_value
     *
     * @return bool
     */
    private function vali_user_id($vali_value)
    {
        if (preg_match("/^[0-9]+$/", $vali_value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证user_token 32位md5
     *
     * @param $vali_value
     *
     * @return bool
     */
    private function vali_user_token($vali_value)
    {
        if (preg_match("/^[0-9a-zA-Z]{32}$/", $vali_value)) {
            return true;
        } else {
            return false;
        }
    }

}
