<?php
//APP用户个人档案
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\admin\model\DoctorModel;
use app\admin\model\AppIntModel;

class Healthrecord extends Controller {
    public function index() {
        $Title = "健康档案";
        $this -> assign('Title',$Title);
        return $this -> fetch('healthRecord/index');
    }
}
?>