<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\model\DoctorModel;
use app\admin\model\DoctorJobModel;
use app\admin\model\DoctorConfeeModel;
use app\admin\model\DoctorLabelModel;
use app\admin\model\AppIntModel;

// header("Content-type: text/html; charset=utf-8");
class Doctor extends Base {
    public function index() {
        //$model = new DoctorModel();
        $model = new AppIntModel('yyb_doctor');
        $model3 = new DoctorModel();
        $title = '医生信息列表';
        $AllData = $model -> AllData([]);
        $pagdata = $model3 -> pagintate2([],10,[]);
        // 获取数据集记录数
        $count = count($AllData);
        //循环数组
        foreach($pagdata as $k => $v) {
            //$k为当前循环的次数(索引)，$v为相对应的内容(数组)
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
			}else if($v['audit_status'] == 'rep'){
                $v['audit_status'] = '<a title="生殖中心"><i class="fa fa-spinner fa-2x"></i></a>';
            }else if($v['audit_status'] == 'test'){
                $v['audit_status'] = '<a title="内部账号"><i class="fa fa-spinner fa-2x"></i></a>';
            }
            // 时间戳转换
            if($v['create_time']==0) {
                $v['create_time'] = "没有数据";
            } else {
                $v['create_time'] = date("Y-m-d H:i",$v['create_time']);
            }
            if($v['update_time']==0) {
                $v['update_time'] = "没有数据";
            } else {
                $v['update_time'] = date("Y-m-d H:i",$v['update_time']);
            }
        }

        $this -> assign('title',$title);
        // 分页输出数据
        $this -> assign('pagdata',$pagdata);
        // 输出总数
        $this -> assign('count',$count);
        return $this -> fetch('doctor/index');
    }
    //添加医生
    public function add() {
        $title = "添加医生";
        $this -> assign('title',$title);
        return $this -> fetch('doctor/doctor_add');
    }
    
    public function adddata() {
        $where = [];
        $data = [];
        $data['mobile'] = $_POST['phone'];
        $data['nick_name'] = $_POST['name'];
        $data['password'] = md5($_POST['password']);
        $data['easemob_username'] = "yyb_doctor_".$_POST['phone'];
        $data['easemob_password'] = "youyunbao111";
        $data['create_time'] = time();
        $model = new AppIntModel('yyb_doctor');
        $return = $model -> AddData($where,$data);
        echo $return;//成功返回1
    }

    //字段搜索
    public function Search() {
        $model = new AppIntModel('yyb_doctor');
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
        $AllData = $model -> AllData([]);
        $pagdata = $model -> pagintate($where,10,$parameter);
        $title = '搜索结果';
        $count = count($AllData);
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
        return $this -> fetch('doctor/index');
    }

    //医生编辑
    public function Alter() {
        $doctor_id = $_GET['doctor_id'];
        $model = new AppIntModel('yyb_doctor');
        $model2 = new DoctorJobModel();
        $model3 = new DoctorConfeeModel();
        $model4 = new DoctorLabelModel();
        //医生基本信息数据
        $where = []; 
        $where['doctor_id'] = $doctor_id;
        $data = $model -> getSelect($where);
        //医生职称数据
        $where = [];
        $where['type'] = 0;
        $doctor_jod_data = $model2 -> getSelect($where);
        //医生科室数据
        $where = [];
        $where['type'] = 1;
        $doctor_department_data = $model2 -> getSelect($where);
        //医生咨询金额数据
        $where = [];
        $where['doctor_id'] = $doctor_id;
        $ConFee_data = $model3 -> getSelect($where);
        //医生咨询金额数据不存在时初始化数据为空
        if($ConFee_data == []) {
            $ConFee_data[0]['text_consulting_1'] = "";
            $ConFee_data[0]['phone_consulting_15min'] = "";
            $ConFee_data[0]['phone_consulting_30min'] = "";
            $ConFee_data[0]['video_consulting_15min'] = "";
            $ConFee_data[0]['video_consulting_30min'] = "";
            $ConFee_data[0]['family_dactor_1month'] = "";
            $ConFee_data[0]['family_dactor_6month'] = "";
            $ConFee_data[0]['family_dactor_1year'] = "";
        }
        //获取医生标签数据
        $where = [];
        //获取全部数据
        $doctor_label_data = $model4 -> getSelect($where);
        //通过医生label_id获取数据
        $Label1 = "";
        $Label2 = "";
        $Label3 = "";
        $arr = explode(",",$data[0]['label_id']);
        if($arr) {
            foreach($arr as $k => $v){
                if($k==0) {$Label1 = $v;}
                if($k==1) {$Label2 = $v;}
                if($k==2) {$Label3 = $v;}
            }
        }
        $DoctorLabel = array($Label1,$Label2,$Label3);
        print_r($DoctorLabel);
        print_r($arr);
        //遍历数据集
        foreach($data as $k => $v) {
            //$k为当前循环的次数，$v为相对应的内容
            if($v['avatar'] == "") {
                //当医生头像为空时，默认的医生头像
                $v['avatar'] = "http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png";
            }
            if($v['province'].$v['city'] == "") {
                $v['province'] = '未填写';
            }
            //医生资格证图片地址
            if($v['qualification_front'] == '') {
                $v['qualification_front'] = "__IMG__/wsc.png";
            }
            if($v['qualification_back'] == '') {
                $v['qualification_back'] = "__IMG__/wsc.png";
            }
            // 时间戳转换
            $v['create_time'] = date("Y-m-d H:i",$v['create_time']);
            $data = $v;
        }
        //页面标题
        $title = "医生信息管理";
        //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        //给模板传递参数
        $this -> assign('title',$title);
        $this -> assign('DoctorData',$data);
        $this -> assign('DoctorJobData',$doctor_jod_data);
        $this -> assign('DoctorLabelData',$doctor_label_data);
        $this -> assign('DoctorLabel',$DoctorLabel);
        $this -> assign('DoctorDepartmentData',$doctor_department_data);
        $this -> assign('ConFee_data',$ConFee_data);
        $this -> assign('AccessKey',$AccessKey);
        $this -> assign('SecretKey',$SecretKey);
        $this -> assign('Bucket',$Bucket);
        $this -> assign('Domain',$Domain);
        $this -> assign('QnToken',$QnToken);
        return $this -> fetch('doctor/doctor_alter');
    }

    //医生编辑修改提交（更新数据）
    public function AlterPost() {
        $model = new AppIntModel('yyb_doctor');
        $model2 = new DoctorJobModel();
        $model3 = new DoctorConfeeModel();
        $where = [];
        //$where = ['doctor_id' => $_POST['doctor_id']];
        $where['doctor_id'] = $_POST['doctor_id'];
        //医生基本信息数据
        $updata = [];
        //医生头像图片链接
        if($_POST['avatar']) {
            $updata['avatar'] = $_POST['avatar'];
        }
        $updata['audit_status'] = $_POST['audit_status'];
        $updata['nick_name'] = $_POST['nick_name'];
        $updata['mobile'] = $_POST['mobile'];
        $updata['email'] = $_POST['email'];
        $updata['id_card'] = $_POST['id_card'];
        $updata['gender'] = $_POST['gender'];
        $updata['title'] = $_POST['title'];
        $updata['province'] = $_POST['province'];
        $updata['city'] = $_POST['city'];
        $updata['intro1'] = $_POST['intro1'];
        $updata['intro2'] = $_POST['intro2'];
        $updata['intro3'] = $_POST['intro3'];
        $updata['intro4'] = $_POST['intro4'];
        $updata['hospital'] = $_POST['hospital'];
        $updata['department_parent'] = $_POST['department_parent'];
        $updata['department_phone'] = $_POST['department_phone'];
        $updata['is_open_image'] = $_POST['is_open_image'];
        $updata['is_open_phone'] = $_POST['is_open_phone'];
        $updata['is_open_video'] = $_POST['is_open_video'];
        $updata['is_open_private'] = $_POST['is_open_private'];
        $updata['label_id'] = $_POST['LabeData'];
        //医生账号登录密码修改
        if($_POST['password']!=='') {
            $updata['password'] = md5($_POST['password']);
        }
        $updata['update_time'] = time();
        //医生咨询金额数据
        $updata2 = [];
        $updata2['doctor_id'] = $_POST['doctor_id'];
        $updata2['text_consulting_1'] = $_POST['text_consulting_1'];
        $updata2['phone_consulting_15min'] = $_POST['phone_consulting_15min'];
        $updata2['phone_consulting_30min'] = $_POST['phone_consulting_30min'];
        $updata2['video_consulting_15min'] = $_POST['video_consulting_15min'];
        $updata2['video_consulting_30min'] = $_POST['video_consulting_30min'];
        $updata2['family_dactor_1month'] = $_POST['family_dactor_1month'];
        $updata2['family_dactor_6month'] = $_POST['family_dactor_6month'];
        $updata2['family_dactor_1year'] = $_POST['family_dactor_1year'];
        //修改医生基本信息数据
        $return = $model -> UpData($where,$updata);
        //查询指定医生咨询金额数据是否存在
        $Tj = $model3 -> getSelect($where);
        if(empty($Tj)) {
            //新建操作
            $model3 -> AddData([],$updata2);
        } else {
            //修改操作
            $where = [];
            $where['doctor_id'] = $_POST['doctor_id'];
            $model3 -> UpData($where,$updata2);
        }
        echo $updata['nick_name']."医生的数据修改成功!";
    }

    public function Qiniu() {
        //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        echo $QnToken;
    }
    //医生删除
    public function delete() {
        $model = new AppIntModel('yyb_doctor');
        $where = [];
        $where['doctor_id'] = $_POST['id'];
        $model -> DeleteData($where);
        echo "ID为：".$where['doctor_id']."的数据删除成功";
    }
}

?>