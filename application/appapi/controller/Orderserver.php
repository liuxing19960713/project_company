<?php
//App咨询订单是否过期自动查询脚本，需要通过外部访问方式启动
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
class Orderserver extends Controller {
    public function open() {
        echo "OK";
    }
}