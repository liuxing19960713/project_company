<?php
//APP搜索关键词管理控制器
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
class Appannouncements extends Base {
    // APP公告推送管理
    public function index() {
        $title = "APP公告推送管理";
        $type = isset($_GET['type'])?$_GET['type']:'0';
        //全部数据
        if($type=='0') {
            $data = AppIntModel::getSelect('yyb_appnotice',[]);
        }
        //用户端数据
        if($type=='1') {
            $data = AppIntModel::getSelect('yyb_appnotice',['sub_type'=>'user']);
        }   
        //医生端数据
        if($type=='2') {
            $data = AppIntModel::getSelect('yyb_appnotice',['sub_type'=>'doctor']);
        }
        //数据处理：
        $length = count($data);
        for($k=0; $k<$length; $k++) {
            $data[$k]['time'] = date("Y-m-d H:i",$data[$k]['time']);
        }
        $this -> assign('type',$type);
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('appannouncements/index');
    }
    //APP公告新增页面
    public function add() {
        $title = "APP公告新增";
        $this -> assign('title',$title);
        return $this -> fetch('appannouncements/add');
    }
    //APP公告编辑
    public function editor() {
        $id = $_GET['id'];
        $title = "APP公告编辑";
        $data = AppIntModel::getSelect('yyb_appnotice',[
            'id' => $id,
        ]);
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('appannouncements/editor');
    }
    //APP公告编辑数据提交
    public function datasubmitted() {
        $reutrn = AppIntModel::UpData('yyb_appnotice',[
            'id' => $_POST['id']
        ],[
            'title' => $_POST['title'],
            'sub_type' => $_POST['sub_type'],
            'show' => $_POST['show'],
            'content' => $_POST['content'],
            'time' => time()
        ]);
        if($reutrn == 1) {
            AppIntModel::jsonReturn('提交成功');
        }
    }
    //APP公告新建数据提交
    public function datasubmitted2() {
        $reutrn = AppIntModel::AddData('yyb_appnotice',[],[
            'title' => $_POST['title'],
            'sub_type' => $_POST['sub_type'],
            'show' => $_POST['show'],
            'content' => $_POST['content'],
            'time' => time()
        ]);
        if($reutrn == 1) {
            //获取新添加的公告
            $data = AppIntModel::getSelect('yyb_appnotice',[
                'title' => $_POST['title'],
                'sub_type' => $_POST['sub_type'],
                'show' => $_POST['show'],
                'content' => $_POST['content'],
                'time' => time()
            ]);
            // //获取所有用户
            // $userData = AppIntModel::getSelect('yyb_user',[]);
            // //虚幻添加id数据
            // foreach($userData as $i => $val) {
            //     AppIntModel::UpData('yyb_user',[
            //         'user_id' => $val['user_id']
            //     ],[
            //         'appnotice_all' => $val['appnotice_all'].','.$data[0]['id']
            //     ]);
            // }
            AppIntModel::jsonReturn('提交成功');
        }
    }
    //APP公告数据删除
    public function deletedata() {
        $return = AppIntModel::DeleteData('yyb_appnotice',[
            'id' => $_POST['id'],
        ]);
        AppIntModel::jsonReturn($return);
    }
}
?> 