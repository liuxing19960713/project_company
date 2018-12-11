<?php
//2018.7.27活动控制器
namespace app\activity\controller;
use think\Controller;
use think\Db;
use app\admin\model\AppIntModel;
use app\admin\model\DoctorModel;
//环信接口:
use app\common\tools\Easemob;

class Actindex727 extends Controller{
    //首页
    public function index(){
        //$member = new AppIntModel('表名');
        //$data = $member -> getAll();
        $data = [];
        $title = "优孕宝最新活动：情系湛江，助孕公益行";
        //绑定数据
        $this -> assign('Title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('727/Index');
    }
    public function doctorlogin() {
        $desk = new AppIntModel('yyb_doctor_job');
        $desk = $desk -> AllData([
            'type' => 1
        ]);
        $title = "医生注册、登录";
        $this -> assign('Title',$title);
        $this -> assign('desk',$desk);
        return $this -> fetch('727/DoctorLogin');
    }
    //医生登录验证
    public function doctorlogin2() {
        $doctor = new AppIntModel('yyb_doctor');
        $where = [
            'mobile' => $_POST['phone'],
            'password' => md5($_POST['password'])
        ];
        $return = $doctor -> getSelect($where);
        if($return==[]) {
            echo "0";
        } else {
            echo $return[0]['doctor_id'];
        }
    }
    //医生端找回密码
    public function ForPassword() {
        $title = "找回密码";
        $this -> assign('Title',$title);
        return $this -> fetch('727/ForPassword');
    }
    //医生二维码
    public function doctorQR() {
        //二维码url
        $QRurl = "http://unkonwn.uyihui.cn/activity/Actindex727/index?doctorid=".$_GET['doctorid'];
        $title = "我的患者";
        $CodeTable = new AppIntModel('yyb_activity727');
        $where = [
            'doctorid'=>$_GET['doctorid']
        ];
        $CodeData = $CodeTable -> getSelect($where);
        // 获取数据集记录数
        $count = count($CodeData);
        //分页获取数据
        $Data = $CodeTable -> pagintate($where,5,['doctorid'=>$_GET['doctorid']]);
        $return = 1;
        if($CodeData==[]){
            $return = 0;
        }
        $this -> assign('Title',$title);
        $this -> assign('QRurl',$QRurl);
        $this -> assign('CodeData',$CodeData);
        $this -> assign('Data',$Data);
        $this -> assign('return',$return);
        return $this -> fetch('727/DoctorQR');
    }
    //医生数据查询
    public function dataquery() {
        $title = "活动数据查询";
        $this -> assign('title',$title);
        if($_GET['type']=='doctor') {
            $title2 = "活动入驻医生数据";
            $DcModel = new DoctorModel();
            $where = [
                'source'=>'activity727'
            ];
            $AllData = $DcModel -> AllData($where);
            //$Data = $DcModel -> pagintate($where,10,['type' => 'doctor']);
            $Data = $DcModel -> AllData($where);
            // 获取数据集记录数
            $count = count($AllData);
            $returnData = [];
            foreach($Data as $k => $v) {
                $returnData[$k] = [
                    'name' => $v['nick_name'],
                    'phone' => $v['mobile'],
                    'time' => $v['create_time'],  
                    'url' => 'http://unkonwn.uyihui.cn/activity/Actindex727/dataquery?type=user&doctorid='.$v['doctor_id'],
                ];
            }
            $this -> assign('Data',$Data);
            $this -> assign('count',$count);
            $this -> assign('returnData',$returnData);
            $this -> assign('title2',$title2);
            return $this -> fetch('727/DataQuery');
        } else if($_GET['type']=='user') {
            $UserModel = new AppIntModel('yyb_activity727');
            $where = [
                'doctorid' => $_GET['doctorid']
            ];
            $returnData = $UserModel -> AllData($where);
            if($returnData==[]) {
                $none = 0;
            } else {
                $none = 1;
            }
            $DcModel = new DoctorModel();
            $where = [
                'doctor_id'=>$_GET['doctorid']
            ];
            $Data = $DcModel -> AllData($where);
            $title2 = "绑定".$Data[0]['nick_name']."医生的用户数据";
            $this -> assign('none',$none);
            $this -> assign('returnData',$returnData);
            $this -> assign('title2',$title2);
            return $this -> fetch('727/DataQuery2');
        }
    }
    //验证验证码
    public function DxCode($a,$b) {
        $CodeTable = new AppIntModel('yyb_code');
        $where = [
            'phone' => $a,
            'code' => $b
        ];
        $Code = $CodeTable -> AllData($where);
        $time = $Code[0]['lasttime'];
        $currenttime = time();
        if($time) {
            $time =  $currenttime - $time;
            if($time <= 300) {
                return 2;
            }
        }
        if($Code==[]) {
            return 0;
        } else {
            $id = $Code[0]['id'];
            $where = [];
            $where = [
                'id' => $id
            ];
            $T = $CodeTable -> DeleteData($where);
            return 1;
        }
    }
    //验证验证码2
    public function DxCode2() {
        $CodeTable = new AppIntModel('yyb_code');
        $where = [
            'phone' => $_POST['phone'],
            'code' => $_POST['code']
        ];
        $Code = $CodeTable -> AllData($where);
        if($Code==[]) {
            //验证码错误
            echo 0;
            //echo $CodeTable -> SQL();
            return;
        }
        $time = $Code[0]['lasttime'];
        $currenttime = time();
        if($time) {
            $time =  $currenttime - $time;
            if($time >= 300) {
                //验证码过期
                echo 2;
                return;
            }
        }
        //验证码正确
        echo 1;
        //删除验证码相应数据，时器失效
        $id = $Code[0]['id'];
        $T = $CodeTable -> DeleteData(['id'=> $id]);   
    }
    //用户数据绑定
    public function usersbinding() {
        //查询用户是否已报名
        $CodeTable = new AppIntModel('yyb_activity727');
        $where = [
            'phone' => $_POST['phone']
        ];
        $Cx = $CodeTable -> getSelect($where);
        if(!$Cx==[]) {
            echo '手机号已存在';
            exit;
        }
        //数据入库
        $data = [
            'doctorid' => $_POST['doctorid'],
            'phone' => $_POST['phone'],
            'name' => $_POST['name'],
            'sex' => $_POST['sex'],
            'age' => $_POST['age'],
            'addtime' => date('Y-m-d H:i:s',time())
        ];
        $where = [];
        $return = $CodeTable -> AddData($where,$data);
        echo "报名成功！";
    }
    //医生注册 
    public function doctorsignin() {
        $DxCode = $this -> DxCode($_POST['phone'],$_POST['code']);
        // if($DxCode==0) {
        //     echo "0";
        //     return;
        // }
        // if($DxCode==2) {
        //     echo "2";
        //     return;
        // }
        //搜索是否存在这个医生的数据
        $member = new DoctorModel();
        $where = [
            'mobile'=>$_POST['phone'],
            //'nick_name' => $_POST['name']
        ];
        $data = $member -> getSelect($where);
        if($data==[]) {
            //新建医生数据
            $where = [];
            $data2 = [
                'mobile' => $_POST['phone'],
                'nick_name' => $_POST['name'],
                'department_parent' => $_POST['desk'],
                'password' => md5($_POST['password']),
                'source' => "activity727",
                //注册时间
                'create_time' => time()
            ];
            $return = $member -> AddData($where,$data2);
            $data = [];
            $data = $member -> AllData(['mobile' => $_POST['phone']]);
            //环信账号
            $easemobAccount = [];
            $easeMob = new Easemob();
            $token = $easeMob->getToken();
            $easemobAccount = $easeMob->regChatUser($token,$data[0]['doctor_id'],'doctor');
            $data2 = [
                'easemob_username' => $easemobAccount['username'],
                'easemob_password' => $easemobAccount['password']
            ];
            $where = [
                'doctor_id' => $data[0]['doctor_id']
            ];
            $return = $member -> UpData($where,$data2);
            echo $data[0]['doctor_id'];
        } else {
            echo "false";
            exit;
        }
    }
    //请求验证码
    public function code1() {
        $code = "";
        for($i=0;$i<4;$i++){
            $code .= rand(0,9);
        }
        $mobile = $_POST['mobile'];
        $table = new AppIntModel('yyb_code');
        $cz = $table -> AllData(['phone'=>$mobile]);
        if($cz) {
            //判断验证码获取时间是否已经超过60秒
            $time = $cz[0]['lasttime'];
            $currenttime = time();
            if($time) {
                $time =  $currenttime - $time;
                if($time <= 60) {
                    echo 2;
                    return;
                }
            }
            //更新验证码数据
            $where = [
                'phone'=>$mobile
            ];
            $data = [
                'code'=>$code,
                'lasttime'=>time()
            ];
            $table -> UpData($where,$data);
        } else {
            //新建验证码数据
            $where = [];
            $data = [
                'phone'=>$mobile,
                'code'=>$code,
                'lasttime'=>time()
            ];
            $table -> AddData($where,$data);
        }
        $url = "http://api.uyihui.cn/api/dysms/api_demo/SmsDemo.php";    
        $data = array("mobile"=>$mobile,"code"=>$code); 
        $posturl = $this->curl_post_https($url, $data); 
        if($posturl == "触发小时级流控Permits:5"){
            // 失败
            echo 0;
        }else{
            // 成功
            echo 1;
        }
    }
    //修改密码
    public function updatapasswprd() {
        $doctorTable = new AppIntModel('yyb_doctor');
        $phone = $_POST['phone'];
        $password = md5($_POST['password']);
        $doctor = $doctorTable -> AllData([
            'mobile' => $phone
        ]);
        if($doctor) {
            //修改密码成功
            $where = ['mobile'=>$phone];
            $data2 = ['mobile'=>$phone];
            $doctorTable -> UpData($where,$data2);
            echo 1;
        } else {
            //账号不存在
            echo 0;
        }
    }
    //php发送post请求
    public function curl_post_https($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在(域名没有开启https协议请勿开启此项！)
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
}
?>