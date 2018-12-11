<?php
namespace app\test\controller;
use think\Controller;
use think\Db;
use app\test\model\TestModel;
class Test extends Controller {
    public function index() {
        $title = "富文本编辑器";



        
        $this -> assign('title',$title);
        return $this -> fetch('test/index');
    }
    //添加医生
    public function add() {
        $title = "添加医生";
        $this -> assign('title',$title);
        return $this -> fetch('test/doctor_add');
    }
    
    public function adddata() {
        $where = [];
        $data = [];
        $data['mobile'] = $_POST['phone'];
        $data['nick_name'] = $_POST['name'];
        $data['password'] = md5($_POST['password']);
        $model = new TestModel();
        $return = $model -> AddData($where,$data);
        echo $return;//成功返回1
    }

    //字段搜索
    public function Search() {
        $model = new TestModel();
        $where = [];
        $parameter = [];
        $phone = input('phone');
        $name = input('name');
        $hospital = input('hospital');
        if($phone) {
            $where['mobile'] = array('like','%'.$_GET['phone'].'%');
            $parameter = ['phone'=>$_GET['phone']];
        }
        if($name) {
            $where['nick_name'] = array('like','%'.$_GET['name'].'%');
            $parameter = ['name'=>$_GET['name']];
        }
        if($hospital) {
            $where['hospital'] = array('like','%'.$_GET['hospital'].'%');
            $parameter = ['hospital'=>$_GET['hospital']];
        }
        $pagdata = $model -> pagintate($where,10,$parameter);
        $title = '搜索结果';
        $count = count($pagdata);
        // 遍历数据集
        foreach($pagdata as $k => $v) {
            //$k为当前循环的次数，$v为相对应的内容
            if($v['hospital'] == "") {
                $v['hospital'] = '<span style="color:#ff2251">未填写</span>';
            }
            if($v['nick_name'] == "") {
                $v['nick_name'] = '<span style="color:#ff2251">未填写</span>';
            }
            if($v['avatar'] == "") {
                $v['avatar'] = "http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png";
            }
            if($v['province'].$v['city'] == "") {
                $v['province'] = '<span style="color:#ff2251">未填写</span>';
            }
            // 修改输出性别数据
            if($v['gender'] == "male") {
                $v['gender'] = "男";
            } else if ($v['gender'] == "female") {
                $v['gender'] = "女";
            } else {
                $v['gender'] = '<span style="color:#ff2251">未填写</span>';
            }
            // 认证转换
            if($v['audit_status'] == 'yes') {
                $v['audit_status'] = '<a style="color:#1ab394" title="已认证"><i class="fa fa-check fa-2x"></i></a>';
            }else if($v['audit_status'] == 'no') {
                $v['audit_status'] = '<a style="color:#ec4758" title="未通过认证"><i class="fa fa-close fa-2x"></i></a>';
            }else if($v['audit_status'] == 'wait') {
                $v['audit_status'] = '<a style="color:#337ab7" title="未审核认证"><i class="fa fa-question fa-2x"></i></a>';
            }else if($v['audit_status'] == 'emp') {
                $v['audit_status'] = '<a title="未提交资料"><i class="fa fa-spinner fa-2x"></i></a>';
            }
            // 时间戳转换
            $v['create_time'] = date("Y-m-d H:i",$v['create_time']);
        }
        $this -> assign('title',$title);
        // 分页输出数据
        $this -> assign('pagdata',$pagdata);
        // 输出总数
        $this -> assign('count',$count);
        return $this -> fetch('test/index');
    }

    //医生编辑
    public function alter() {
        $doctor_id = $_GET['doctor_id'];
        $model = new TestModel();
        $where = [];
        $where['doctor_id'] = $doctor_id;
        $data = $model -> getSelect($where);
        //遍历数据集
        foreach($data as $k => $v) {
            //$k为当前循环的次数，$v为相对应的内容
            if($v['avatar'] == "") {
                $v['avatar'] = "http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png";
            }
            if($v['province'].$v['city'] == "") {
                $v['province'] = '未填写';
            }
            // 修改输出性别数据
            if($v['gender'] == "male") {
                $v['gender'] = "男";
            } else if ($v['gender'] == "female") {
                $v['gender'] = "女";
            } else {
                $v['gender'] = '<span style="color:#ff2251">未填写</span>';
            }
            // 认证转换
            if($v['audit_status'] == 'yes') {
                $v['audit_status'] = '<a style="color:#1ab394" title="已认证"><i class="fa fa-check fa-2x"></i></a>';
            }else if($v['audit_status'] == 'no') {
                $v['audit_status'] = '<a style="color:#ec4758" title="未通过认证"><i class="fa fa-close fa-2x"></i></a>';
            }else if($v['audit_status'] == 'wait') {
                $v['audit_status'] = '<a style="color:#337ab7" title="未审核认证"><i class="fa fa-question fa-2x"></i></a>';
            }else if($v['audit_status'] == 'emp') {
                $v['audit_status'] = '<a title="未提交资料"><i class="fa fa-spinner fa-2x"></i></a>';
            }
            // 时间戳转换
            $v['create_time'] = date("Y-m-d H:i",$v['create_time']);
        }
        $title = "医生信息管理";
        $this -> assign('title',$title);
        $this -> assign('DoctorData',$data);
        return $this -> fetch('test/doctor_alter');
    }

    //医生删除
    public function delete() {
        $model = new TestModel();
        $where = [];
        $where['doctor_id'] = $_POST['id'];
        $model -> DeleteData($where);
        echo "ID为：".$where['doctor_id']."的数据删除成功";
    }
}

?>