<?php
//App活动管理控制器
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\model\AppIntModel;
class Activity extends Base {
    //活动管理首页
    public function index() {
        $title = '活动管理';
        $this -> assign('title',$title);
        return $this -> fetch('activity/index');
    }
}
?>