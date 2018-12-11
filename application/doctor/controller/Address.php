<?php
namespace app\doctor\controller;

use think\Controller;
use app\index\controller\Base;
use think\Db;

class Address extends Base
{
    public function index()
    {
        header('Access-Control-Allow-Origin:*');
        if ($_GET) {
            $where = "parentId = " . $_GET['parenrId'];
        }
        $where = empty($where) ? "level = '1'" : $where;
        $address = Db::name('address')->where($where)->select();
        if ($address) {
            return $this->private_result('0001', array('address' => $address));
        } else {
            return $this->private_result('10001');
        }
    }

    public function department()
    {
        $file1 = 'D:\xampp\htdocs\yyb\department.json';
        $content = file_get_contents($file1);
        $data = json_decode($content,JSON_UNESCAPED_UNICODE);
        var_dump($data);

    }
}