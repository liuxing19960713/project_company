<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/12/5
 * Time: 10:41
 * Comments: 聊天常用模板
 */
namespace app\doctor\controller;

use think\Db;
use think\Controller;
use think\Exception;
use app\index\controller\Base;
use app\doctor\model\User as UserModel;
use app\doctor\model\ChatTemplates as ChatModel;

class ChatTemplates extends Base
{
    public $ChatModel;
    public $UserModel;

    public function __construct()
    {
        parent::__construct();
        $this->ChatModel = new ChatModel();
        $this->UserModel = new UserModel();
    }

    public function get()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $data = $this->ChatModel->get(array("d_id" => $d_id));
        if ($data !== false) {
            return $this->private_result(RESPONSE_SUCCESS, $data);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function insert()
    {
        $token = get_token();
        $content = safe_str(get_post_value('content', ''));

        // 验参
        $validateResult = strlen($content) <= 200;
        if (empty($token) || empty($content)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $data['d_id'] = $d_id;
        $data['create_time'] = time();
        $data['content'] = $content;
        $data = $this->ChatModel->insert($data);
        if ($data !== false) {
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function delete()
    {
        $token = get_token();
        $ct_id = get_post_value('ct_id', '');

        // 验参
        $validateResult = validate_number($ct_id);
        if (empty($token) || empty($ct_id)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $where['d_id'] = $d_id;
        $where['ct_id'] = $ct_id;
        $data = $this->ChatModel->delete($where);
        if ($data !== false) {
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function update()
    {

        $token = get_token();
        $ct_id = get_post_value('ct_id', '');
        $content = safe_str(get_post_value('content', ''));
        // 验参
        $validateResult = validate_number($ct_id) && (strlen($content) <= 200);
        if (empty($token) || empty($ct_id) || empty($content)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $where['d_id'] = $d_id;
        $where['ct_id'] = $ct_id;
        $save['content'] = $content;
        $data = $this->ChatModel->update($where, $save);
        if ($data !== false) {
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }
}
