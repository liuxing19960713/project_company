<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Post extends Validate
{
    protected $rule = [
        'post_type' => 'require|in:normal,exp,help',
        'group_type' => 'require|in:0,1,2,3,4',
        'title' => 'require|max:150',
        'content' => 'require',
        'is_for_hospital' => 'checkHos',
        'user_id' => 'require|number|gt:0',
        'post_id' => 'require|number|gt:0',
        'tab_type' => 'require|in:all,newest,exp,help,best',
        'page_index' => 'require|number|gt:0',
        'page_size' => 'require|number|gt:0',
        'hospital_id_req' => 'require|max:32',
    ];

    protected $message = [
        'post_type.require' => '请选择帖子类型',
        'post_type.in' => '帖子类型错误',
        'group_type' => '请选择圈子类型',
        'title.require' => '标题不能为空',
        'title.max' => '标题太长了',
        'content' => '内容不能为空',
        'is_for_hospital' => '类型参数错误',
        'user_id' => '用户参数错误',
        'post_id' => '请求参数错误',
        'tab_type' => '请求参数错误',
        'page_index' => '页码要大于0哦',
        'page_size' => '每页数量要大于0哦',
        'hospital_id_req' => '医院id参数错误'
    ];

    protected $scene = [
        'store' => ['post_type', 'group_type', 'title', 'content', 'is_for_hospital'],
        'model_store' => ['post_type', 'group_type', 'title', 'content', 'is_for_hospital', 'user_id'],
        'show' => ['post_id'],
        'page_index' => ['page_index'],
        'page_size' => ['page_size'],
        'index' => ['group_type', 'tab_type'],
        'hospital' => ['hospital_id_req'],
        'post_type' => ['post_type'],
    ];

    protected function checkYn($value, $rule, $data)
    {
        $allowValue = [
            true, 'yes', '1', 1,
            false, 'no', '0', 0
        ];
        foreach ($allowValue as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return '参数不符合' . var_export($data, true);
    }

    protected function checkHos($value, $rule, $data)
    {
        $allowValue = [
            true, 'yes', '1', 1,
            false, 'no', '0', 0
        ];
        $result = false;
        $isForHos = $data['is_for_hospital'];
        foreach ($allowValue as $item) {
            if ($item === $isForHos) {
                $result =  true;
            }
        }
        if (!$result) {
            return '医院类型参数不符合';
        }

        if ($isForHos == 'no') {
            return true;
        }

        if ($isForHos) {
            if (empty($data['hospital_id'])) {
                return '医院id不能为空';
            }
            if (strlen($data['hospital_id']) > 32) {
                return '医院id太长';
            }
        }
        return true;
    }
}
