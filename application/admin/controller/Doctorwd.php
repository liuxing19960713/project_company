<?php
//优孕宝医生端医生提现申请
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
class Doctorwd extends Base {
    public function index() {
        //获取提现数据
        $data = AppIntModel::AllData('yyb_doctor_destoon',[]);
        $title = '医生提现申请审核';
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('doctorwd/index');
    }
}
?> 