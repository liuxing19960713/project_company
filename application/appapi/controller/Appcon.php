<?php
//app公用数据接口
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
//use app\appapi\controller\Wsapi;
//环信接口: 
use app\common\tools\Easemob;
header("Access-Control-Allow-Origin:*");
class Appcon extends Controller {
    /**
     * 工具方法
     * */ 
    //页面输出json格式数据
    protected function jsonReturn($data) {
        header('Content-Type:application/json');
        $returndata = json_encode($data);
        echo $returndata;
    }
    //html5 websocket双向通信通道
    public function websocket() {  }
    /**
     * 我的服务
     * */
    //数据拉取
    public function Myservicedatapull() {
        //医生是否开启图文咨询
        $DoctorData = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id'],
        ]);
        $Return = [];
        //是否开启服务
        $Return['is_open_image'] = $DoctorData[0]['is_open_image'];
        //图文咨询价格
        $Return['image_price'] = $DoctorData[0]['image_price'];
        AppIntModel::jsonReturn($Return);
    }
    //状态保存
    public function Myservicedatapush() {
        $Return = AppIntModel::UpData('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id'],
        ],[
            'is_open_image' => $_POST['is_open_image'],
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //价格保存
    public function Myservicedatapush2() {
        $Return = AppIntModel::UpData('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id'],
        ],[
            'image_price' => $_POST['image_price'],
        ]);
        AppIntModel::jsonReturn($Return);
    }
    /**
     * 身份审核
     * */
    //获取医生科室,职称名称数据
    public function Doctorjob() {
        $Return = AppIntModel::getSelect('yyb_doctor_job',[]);
        AppIntModel::jsonReturn($Return);
    }
    //审核数据保存(基本信息)
    public function Auditsave() {
        //$DoctorData = $this -> yyb_doctor($_POST['token']);
        $Return = AppIntModel::UpData('yyb_doctor',[
            'login_token' => $_POST['token']
        ],[
            'nick_name' => $_POST['nick_name'],
            'hospital' => $_POST['hospital'],
            'department_parent' => $_POST['department_parent'],
            'title' => $_POST['title'],
            'province' => $_POST['province'],
            'city' => $_POST['city'],
            'intro1' => $_POST['intro1']
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //审核数据保存(资质证书)
    public function Auditsave2() {
        //$DoctorData = $this -> yyb_doctor($_POST['token']);
        $Return = AppIntModel::UpData('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id']
        ],[
            'qualification_back' => $_POST['qualification_back'],
            'qualification_front' => $_POST['qualification_front'],
            'audit_status' => 'wait'
        ]);
        AppIntModel::jsonReturn($Return);
    }
    /**
     * 用户注册、登录
     * */ 
    //获取验证码
    //状态码：0表示发送失败；1表示发送成功；2表示距离上次获取验证码还没有超过60秒；
    public function Code() {
        $mobile = $_POST['phone'];
        //生成随机验证码
        $code = "";
        for($i=0; $i<4; $i++){
            $code .= rand(0,9);
        }
        //查询上次获取验证码的时间
        $cz = AppIntModel::getSelect('yyb_code',[
            'phone' => $mobile
        ]);
        if($cz) {
            //判断验证码获取时间是否已经超过60秒
            $lasttime = $cz[0]['lasttime'];
            $currenttime = time();
            if($lasttime) {
                $time =  $currenttime - $lasttime;
                if($time <= 60) {
                    //返回状态码:2
                    AppIntModel::jsonReturn(2);
                    return;
                } else {
                    //更新相应用户的验证码数据
                    AppIntModel::UpData('yyb_code',[
                        'phone' => $mobile
                    ],[
                        'code'=>$code,
                        'lasttime'=>time()
                    ]);
                }
            }
        } else {
            //没有历史验证码数据，新建验证码数据
            AppIntModel::AddData('yyb_code',[],[
                'phone' => $mobile,
                'code' => $code,
                'lasttime' => time()
            ]);
        }
        //使用短信接口发送验证码
        $url = "http://api.uyihui.cn/api/dysms/api_demo/SmsDemo.php";    
        $data = array("mobile"=>$mobile,"code"=>$code); 
        //发送post请求给接口
        $posturl = $this -> curl_post_https($url, $data); 
        if($posturl == "触发小时级流控Permits:5"){
            // 发送失败
            AppIntModel::jsonReturn(0);
        }else{
            // 发送成功
            AppIntModel::jsonReturn(1);
        }
    }
    //医生端用户token生成
    public function Logintoken($phone) {
        return md5($phone.time());
    }
    //医生端用户登录
    public function Login() {
        //获取数据
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        //验证手机和密码
        $verify = AppIntModel::getSelect("yyb_doctor",[
            'mobile' => $phone,
            'password' => $password
        ]);
        //账号或密码错误
        if ($verify == []) {
            $this -> jsonReturn(0);
            return;
        } else {
            //账号或密码正确
            //生成token
            $login_token = $this -> Logintoken($_POST['phone']);
            //数据写入
            AppIntModel::UpData("yyb_doctor",['mobile'=>$phone],['login_token'=>$login_token]);
            //设置json格式输出
            $this -> jsonReturn($login_token);
        }
    }
    //医生端用户token验证（查询现有token是否有效）
    public function Verifytoken() {
        $result = AppIntModel::getSelect('yyb_doctor',[
            'login_token' => $_POST['token'],
        ]);
        if($result==[]) {
            $this -> jsonReturn(0);
        } else {
            //通过审核
            if($result[0]['audit_status']=='yes') {$this -> jsonReturn(1);}
            //审核中
            if($result[0]['audit_status']=='wait') {$this -> jsonReturn(1);}
            //测试账号
            if($result[0]['audit_status']=='test') {$this -> jsonReturn(1);}
            //未提交资料
            if($result[0]['audit_status']=='emp') {$this -> jsonReturn(2);}
            //审核不通
            if($result[0]['audit_status']=='no') {$this -> jsonReturn(3);}
        }
    }
    //医生端用户注册
    //状态码：0该手机号已被注册；2验证码不正确；
    public function Register() {
        //查询验证码是否正确
        $Yz = AppIntModel::getSelect('yyb_code',[
            'phone' => $_POST['phone'],
            'code' => $_POST['code']
        ]);
        if($Yz) {
            //验证码验证通过，删除验证码相应数据
            AppIntModel::DeleteData('yyb_code',['phone' => $_POST['phone']]);
        } else {
            //验证码不正确
            AppIntModel::jsonReturn(2);
            return;
        }
        //查询是否已经存在这个医生用户
        $Dc = AppIntModel::getSelect('yyb_doctor',['mobile' => $_POST['phone']]);
        if($Dc) {
            //该手机号已被注册
            AppIntModel::jsonReturn(0);
            return;
        } else {
            //新增医生用户数据
            $doctorid = AppIntModel::AddDataID('yyb_doctor',[],[
                'mobile' => $_POST['phone'],
                'password' => md5($_POST['password']),
                'source' => "web",
                'create_time' => time(),
            ]);
            //新增环信账号
            $easemobAccount = [];
            $options['client_id']='YXA6n2EzUKfcEeakoZ90MejBhw';
            $options['client_secret']='YXA6_60uIDrWt6zGjrYXrFx3wQFm_vY';
            $options['org_name']='1161161111115389';
            $options['app_name']='yyb';
            $easeMob = new Easemob($options);
            $easemobAccount = $easeMob -> createUser('yyb_doctor_'.$doctorid,'youyunbao111');
            $easemobAccount['username'] = 'yyb_doctor_'.$doctorid;
            $easemobAccount['password'] = 'youyunbao111';
            //随机生成医生邀请码(唯一且不重复)
            $invite_code = AppIntModel::createCode($doctorid.time());
            //给医生添加邀请码以及环信账号
            AppIntModel::UpData('yyb_doctor',[
                'doctor_id' => $doctorid
            ],[
                'invite_code' => $invite_code,
                'easemob_username' => $easemobAccount['username'],
                'easemob_password' => $easemobAccount['password']
            ]);
            AppIntModel::jsonReturn(1);
        }
    }
    //重设密码
    public function Register2() {
        //查询验证码是否正确
        $Yz = AppIntModel::getSelect('yyb_code',[
            'phone' => $_POST['phone'],
            'code' => $_POST['code']
        ]);
        if($Yz) {
            //验证码验证通过，删除验证码相应数据
            AppIntModel::DeleteData('yyb_code',[
                'phone' => $_POST['phone']
            ]);
        } else {
            //验证码不正确
            AppIntModel::jsonReturn(2);
            return;
        }
        //更新密码
        $Return = AppIntModel::UpData('yyb_doctor',[
            'mobile' => $_POST['phone']
        ],[
            'password' => md5($_POST['password'])
        ]);
        AppIntModel::jsonReturn($Return);
    }
    /**
     * 其余未整合
     * */ 
    //医生用户数据表（根据token获取相应医生数据）
    public function yyb_doctor($token) {
        $data = AppIntModel::getSelect('yyb_doctor',[
            'login_token' => $token,
        ]);
        $this -> jsonReturn($data);
    }
    //医生用户数据表（根据doctor_id获取相应医生数据）
    public function yyb_doctor2($id) {
        $data = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $id,
        ]);
        $this -> jsonReturn($data);
    }
    //医生用户数据表（根据doctor_id获取相应医生数据,并return出来）
    public function yyb_doctor_Return($id) {
        $data = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $id,
        ]);
        return $data;
    }
    //医生用户数据表外部接口（根据token获取相应医生数据）
    public function yyb_doctor3() {
        $token = $_POST['token'];
        $data = AppIntModel::getSelect('yyb_doctor',[
            'login_token' => $token,
        ]);
        $this -> jsonReturn($data);
    }
    //患者用户数据表（根据用户id来完成查询操作）
    public function yyb_user($id) {
        $data = AppIntModel::getSelect('yyb_user',[
            'user_id' => $id,
        ]);
        $this -> jsonReturn($data);
    }
    //患者用户数据表（根据用户id来完成查询操作,并return出来）
    public function yyb_user2($id) {
        $data = AppIntModel::getSelect('yyb_user',[
            'user_id' => $id,
        ]);
        return $data;
    }
    //咨询数据登录接口
    public function Consult() {
        $doctorid = $_POST['doctorid'];
        $userid = $_POST['userid'];
        $c_id = $_POST['c_id'];
        $doctordata = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $doctorid,
        ]);
        $userdata = AppIntModel::getSelect('yyb_user',[
            'user_id' => $userid,
        ]);
        $consultationData = AppIntModel::getSelect('yyb_consultation',[
            'con_id' => $c_id,
        ]);
        $returndata = [
            $doctordata[0],$userdata[0],$consultationData[0]
        ];
        $this -> jsonReturn($returndata);
    }
    //医生端Banner表
    public function banner() {
        $data = AppIntModel::AllData('yyb_banner_doctor',[]);
        $returndata = [];
        foreach($data as $k => $v) {
            $returndata[$k]['img_url'] = $v['img_url'];
            $returndata[$k]['href_url'] = $v['href_url'];
        }
        $this -> jsonReturn($returndata);
    }
    //咨询订单表(医生端首页今日订单数据)
    public function consultation() {
        $token = $_POST['token'];
        $doctorData = AppIntModel::AllData('yyb_doctor',[
            "login_token" => $token,
        ]);
        $consultationData = AppIntModel::AllData('yyb_consultation',[
            'd_id' => $doctorData[0]['doctor_id'],
        ]);
        //没有订单信息
        if(!$consultationData) {
            $this -> jsonReturn(0);
            return;
        }
        //输出订单信息
        $this -> jsonReturn($consultationData);
    }
    //咨询订单表2（处理后详细订单信息与用户信息）
    public function consultation2() {
        $token = $_POST['token'];
        $doctorData = AppIntModel::AllData('yyb_doctor',[
            "login_token" => $token,
        ]);
        $consultationData = AppIntModel::AllData('yyb_consultation',[
            'd_id' => $doctorData[0]['doctor_id'],
        ]);
        //订单数据处理
        foreach($consultationData as $i => $val) {
            //根据订单数据获取相应的用户数据
            $userData = AppIntModel::AllData('yyb_user',[
                'user_id' => $val['c_id'],
            ]);
            //此判断用于防止删除用户数据但订单数据中还存在用户id时,导致程序异常报错
            if($userData!==[]) {
                $consultationData[$i]['TXimgurl'] = $userData[0]['avatar'];
                $consultationData[$i]['username'] = $userData[0]['nick_name'];
                $consultationData[$i]['last_chat_time'] = date("Y-m-d H:i:s",$val['last_chat_time']);
                //重新获取订单最后一句信息，拼接到返回前端的数据中
                $url = "http://api.uyihui.cn/api/api.php?app=consultative&act=get_end";
                //$data为订单id
                $data = [
                    'con_id' => $consultationData[$i]['con_id'],
                    'd_id' => $consultationData[$i]['d_id']
                ];
                $EndMessage = AppIntModel::curl_post_https($url,$data);
                //将json对象转为数组
                $EndMessage = json_decode($EndMessage,1);
                $consultationData[$i]['end_massage'] = $EndMessage['data'][0]['end_massage'];
            }
        }
        //没有订单信息
        if(!$consultationData) {
            $this -> jsonReturn(0);
            return;
        }
        //输出订单信息
        AppIntModel::jsonReturn($consultationData);
    }
    //根据医生id获取环信账号,密码
    public function geteasemob() {
        $return = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id']
        ]);
        AppIntModel::jsonReturn([
            'easemob_username' => $return[0]['easemob_username'],
            'easemob_password' => $return[0]['easemob_password']
        ]);
    }
    //根据信息(医生环信账号、用户环信账号)获取订单数据
    public function getcdata() {
        $doctorData = AppIntModel::AllData('yyb_doctor',[
            'easemob_username' => $_POST['easemob_username']
        ]);
        $userData = AppIntModel::AllData('yyb_user',[
            'easemob_username' => $_POST['easemob_username2']
        ]);
        $return = AppIntModel::AllData('yyb_consultation',[
            'c_id' => $userData[0]['user_id'],
            'd_id' => $doctorData[0]['doctor_id']
        ]);
        AppIntModel::jsonReturn($return);
    }
    //根据订单id获取订单数据
    public function getcdata2($con_id) {
        $return = AppIntModel::AllData('yyb_consultation',[
            'con_id' => $con_id,
        ]);
        AppIntModel::jsonReturn($return);
    }
    //根据订单id获取订单数据(返回数据)
    public function getcdata3($con_id) {
        $return = AppIntModel::AllData('yyb_consultation',[
            'con_id' => $con_id,
        ]);
        return $return;
    }
    /**
     * 提现密码验证,判断相应用户的提现密码是否存在
    **/ 
    public function wd_password_verify() {
        $Data = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id'],
        ]);
        if($Data[0]['wd_password']) {
            AppIntModel::jsonReturn(true);
        } else {
            AppIntModel::jsonReturn(false);
        }
    }
    /**
     * 未读消息
    **/
    //为订单更新未读消息数量
    public function Unread_message() {
        
    }
    //清除未读消息
    public function Ca_Unread_message() {
        $c_id = $_POST['cid'];
        AppIntModel::UpData('yyb_consultation',[
            'con_id' => $_POST['cid']
        ],[
            'Unread_message' => '0'
        ]);
    }   
    //改变订单状态为结束并更新进咨询消息表
    public function EndOrder() {
        //改变订单状态
        AppIntModel::UpData('yyb_consultation',[
            'con_id' => $_POST['cid']
        ],[
            'state' => '已结束'
        ]);
        //获取数据
        $data = AppIntModel::getSelect('yyb_consultation',[
            'con_id' => $_POST['cid']
        ]);
        //查找yyb_notice中是否存在数据
        $data2 = AppIntModel::getSelect('yyb_notice',[
            'con_id' => $_POST['cid'],
            'msg_type' => '1'
        ]);
        //更新数据进咨询消息表
        if($data2) {
            //更新数据
            AppIntModel::UpData('yyb_notice',[
                'con_id' => $_POST['cid'],
                'msg_type' => '1'
            ],[
                'status' => '已结束', //订单状态
                'create_time' => time() //更新时间
            ]);
        } else {
            //新增数据
            AppIntModel::AddData('yyb_notice',[],[
                'con_id' => $_POST['cid'], //订单ID
                'form_id' => $data['c_id'], //发送ID
                'to_id' => $data['d_id'], //接收ID
                'type' => $data['type'], //订单类型
                'status' => '已结束', //订单状态
                'msg_type' => '1', //信息类型(0为用户端信息，1为医生端信息)
                'create_time' => time() //更新时间
            ]);
        }
    }
    //问诊报告
    //保存问诊报告信息
    public function interrogationreport() {
        //获取用户健康档案中的出生日期,计算出用户的实际年龄
        $yyb_hr = AppIntModel::getSelect('yyb_hr',[
            'user_id' => $_POST['u_id'],
        ]);
        $Y = date('Y');
        $birth = intval(substr($yyb_hr[0]['birth'],0,4));
        $age = $birth - $Y;
        //新增问诊报告表数据 
        $AddData = [
            'Illness_description' => $_POST['Illness_description'],
            'diagnoses' => $_POST['diagnoses'],
            'treatment_recommendations' => $_POST['treatment_recommendations'],
            'c_id' => $_POST['c_id'],
            'u_id' => $_POST['u_id'],
            'd_id' => $_POST['d_id'],
            'add_time' => time(),
            'age' => $age,
            'name' => $yyb_hr[0]['name'],
            'sex' => $yyb_hr[0]['gender'],
            'have_children' => $yyb_hr[0]['have_children'],
        ];
        $ReportID = AppIntModel::AddDataID('yyb_interrogation_report',[],$AddData);
        //新增历史消息数据
        AppIntModel::AddData('yyb_webim_msg',[],[
            'type' => 'report',
            'msg_data' => $ReportID,
            'dialog_id' => $_POST['c_id'],
            'time' => time(),
        ]);
        //修改订单状态 并 更新订单表中的问诊报告id数据
        AppIntModel::UpData('yyb_consultation',[
            'con_id' => $_POST['c_id'],
        ],[
            'state' => '已完成',
            'residue' => 0,
            'report_id' => $ReportID
        ]);
        //问诊报告发送到APP用户端
        $data = [
            'id' => $ReportID,
            'u_id' => $_POST['u_id'],
            'd_id' => $_POST['d_id'],
            'c_id' => $_POST['c_id']
        ];
        AppIntModel::curl_post_https('http://api.uyihui.cn/api/api.php?app=consultative&act=report',$data);
        //医生服务次数+1
        AppIntModel::query('update yyb_doctor set service_times = service_times + 1 where doctor_id='.$_POST['d_id']);
        /*医生账户余额增加*/
        //获取订单数据
        $C_data = AppIntModel::getSelect('yyb_consultation',['con_id' => $_POST['c_id']]);
        //获取医生数据
        $D_data = AppIntModel::getSelect('yyb_doctor',['doctor_id' => $_POST['d_id']]);
        //判断订单类型是否不为快速咨询
        if($C_data[0]['Quick']=='0') {
            AppIntModel::UpData('yyb_doctor',[
                'doctor_id' => $_POST['d_id']
            ],[
                //当前余额
                'money' => $D_data[0]['money'] + $C_data[0]['money'],
                //累计收入
                'acc_income' => $D_data[0]['acc_income'] + $C_data[0]['money']
            ]);
            /*医生余额收支记录表增加数据*/
            AppIntModel::AddData('yyb_doctor_balance_record',[],[
                'doctor_id' => $_POST['d_id'],
                'type' => '咨询收入',
                'number' => $C_data[0]['money'],
                'current_balance' => $D_data[0]['money'] + $C_data[0]['money'],
                'add_time' => time()
            ]);
        }
        AppIntModel::jsonReturn(1);
    }
    //获取问诊报告信息(根据问诊报告id)
    public function interrogationreportpull() {
        $return = AppIntModel::getSelect('yyb_interrogation_report',[
            'id' => $_POST['id'],
        ]);
        AppIntModel::jsonReturn($return);
    }
    //获取问诊报告信息(根据医生id)
    public function interrogationreportpull2() {
        $return = AppIntModel::getSelect('yyb_interrogation_report',[
            'd_id' => $_POST['d_id'],
        ]);
        AppIntModel::jsonReturn($return);
    }
    /**
     * 手机,密码修改
     * */ 
    //通过旧密码修改登录密码
    //0:信息不完整; 1:旧密码错误; 2:密码修改成功;
    public function OldPassWord() {
        if($_POST['DoctorID'] && $_POST['OldPassword'] && $_POST['NewPassword']) {
            $doctor_data = AppIntModel::getSelect('yyb_doctor',[
                'doctor_id' => $_POST['DoctorID']
            ]);
            if(md5($_POST['OldPassword']) == $doctor_data[0]['password']) {
                //修改密码
                $DoctorID = AppIntModel::AddDataID('yyb_doctor',[
                    'doctor_id' => $_POST['DoctorID'],
                ],[
                    'password' => md5($_POST['OldPassword']),
                ]);
                AppIntModel::jsonReturn(2);
            } else {
                AppIntModel::jsonReturn(1);
            }
        } else {
            AppIntModel::jsonReturn(0);
        }
    }
    //修改绑定的手机号
    public function ModifyPhone() {
        $DoctorData = AppIntModel::getSelect('yyb_doctor',['doctor_id' => $_POST['doctor_id'],]);
        //判断密码
        if($DoctorData[0]['password']!=md5($_POST['password'])) {
            AppIntModel::jsonReturn(['error','密码错误']); return;
        }
        //查找手机号是否重复
        $Phone = AppIntModel::getSelect('yyb_doctor',['mobile' => $_POST['phone'],]);
        if($Phone) {
            AppIntModel::jsonReturn(['error','新手机号被占用']); return;
        }
        //获取验证码
        $code =  AppIntModel::getSelect('yyb_code',['phone' => $_POST['phone']]);
        //判断验证码
        if($_POST['code']!=$code[0]['code']) {
            AppIntModel::jsonReturn(['error','验证码错误']); return;
        } else {
            //删除验证码数据
            AppIntModel::DeleteData('yyb_code',['phone' => $_POST['phone']]);
        }
        //修改数据
        $Reture = AppIntModel::UpData('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id'],
        ],[
            'mobile' => $_POST['phone'],
        ]);
        if($Reture==1) { AppIntModel::jsonReturn(['success','修改成功']); }
    }
    /**
     * Webim即时聊天辅助接口
    */
    // 上传咨询最后一句话到订单表
    public function upendmassage() {
        header("Content-Type: text/html;charset=utf-8");
        $id = $_POST['id'];
        $message = $_POST['message'];
        AppIntModel::UpData('yyb_consultation',[
            'con_id' => $id,
        ],[
            'end_massage' => $message,
        ]);
    }
    //更改剩余对话条数
    public function responded() {
        $data = AppIntModel::getSelect('yyb_consultation',[
            'con_id' => $_POST['cid']
        ]);
        $residue;
        $last_speaker = $data[0]['last_speaker'];
        if($last_speaker=='0') {
            //直接输出剩余对话次数
            $residue = $data[0]['residue'];
            $this -> jsonReturn($residue);
        }
        if($last_speaker=='1') {
            //修改剩余对话次数后再输出
            if(intval($data[0]['residue']) > 0) {
                $residue = intval($data[0]['residue']) - 1;
            }
            if(intval($data[0]['residue']) <= 0) {
                $residue = 0;
            }
            AppIntModel::UpData('yyb_consultation',[
                'con_id' => $_POST['cid']
            ],[
                'residue' => $residue
            ]);
            $this -> jsonReturn($residue);
        }
    }
    //上传聊天数据到消息表(保存emjio表情数据出错)并更改数据表中的信息最后发送人字段
    public function upmasssage() {
        //修改最后发送人为用户
        if($_POST['last_speaker']=='1') {
            $time = time();
            //更改最后发送人与最后发送时间以及已读状态
            AppIntModel::UpData('yyb_consultation',[
                'con_id' => $_POST['dialog_id'],
            ],[
                'last_speaker' => '1',
                'last_chat_time' => $time,
                'status' => '1'
            ]);
        }
        //修改最后发送人为医生
        if($_POST['last_speaker']=='0') {
            $time = time();
            //判断是否为医生的第一句回复
            $c_data = AppIntModel::getSelect('yyb_consultation',[
                'con_id' => $_POST['dialog_id'],
            ]);
            if($c_data[0]['doctor_responded']=='0') {
                //获取医生名称
                $doctor_id = $c_data[0]['d_id'];
                $doctor_data = AppIntModel::getSelect('yyb_doctor',[
                    'doctor_id' => $doctor_id,
                ]);
                $doctor_name = $doctor_data[0]['nick_name'];
                //获取用户手机号
                $user_id = $c_data[0]['c_id'];
                $user_data = AppIntModel::getSelect('yyb_user',[
                    'user_id' => $user_id,
                ]);
                $mobile = $user_data[0]['mobile'];
                //给用户手机发送医生已回复的提醒短信
                AppIntModel::curl_post_https("http://api.uyihui.cn/api/dysms/api_demo/SmsDemo.php",[
                    'mobile' => $mobile,
                    'name' => $doctor_name,
                    'type' => 4
                ]);
            }
            //更改最后后发送人与最后发送时间
            AppIntModel::UpData('yyb_consultation',[
                'con_id' => $_POST['dialog_id'],
            ],[
                'last_speaker' => '0',
                'doctor_responded' => '1',
                'last_chat_time' => $time
            ]);
        }
    }
    //拉取历史聊天数据(获取emjio表情数据出错)
    public function MessageHistory() {
        header("Content-type:text/html;charset=utf8");
        $a = $_POST['doctor'];
        $b = $_POST['user'];
        $id = $_POST['dialog_id'];
        $return = AppIntModel::getSelect('yyb_webim_msg',[
            'dialog_id' => $id,
        ]);
        //print_r($return);
        $this -> jsonReturn($return);
    }
    //重置咨询列表中已读消息数量
    public function ResetMsgStatus() {
        $return = AppIntModel::UpData('yyb_webim_msg',[
            'dialog_id' => $_POST['dialog_id']
        ],[
            'status' => '1'
        ]);
        AppIntModel::jsonReturn($return);
    }
    //触发订单回访接口（修改订单状态,快速咨询状态清0,剩余对话次数清零）
    public function ConsultOpen() {
        $return = AppIntModel::UpData('yyb_consultation',[
            'con_id' => $_POST['con_id'],
        ],[
            'state' => '医生回访',
            'residue' => 0,
        ]);
        AppIntModel::jsonReturn($return);
    }
    //test
    public function test() {
        AppIntModel::test();
    }
    //触发订单结束事件,修改订单状态
    public function end_conversation() {
        // //更新订单数据表
        $return = AppIntModel::UpData('yyb_consultation',[
            'con_id' => $_POST['con_id'],
        ],[
            'state' => '已完成',
            'residue' => 0,
        ]);
        //更新消息中心数据
        $this -> AddMsgCenterData($_POST['con_id'],$_POST['u_id'],$_POST['d_id'],'咨询结束');
        //if($return) {AppIntModel::jsonReturn($return);}
    }
    //根据医生id获取全部未读消息数量
    public function UnreadMessagesAll() {
        //获取医生的环信号
        $doctor_data = AppIntModel::getSelect('yyb_doctor',['doctor_id'=>$_POST['doctor_id']]);
        $easemob_username = $doctor_data[0]['easemob_username'];
        $msg_data = AppIntModel::getSelect('yyb_webim_msg',[
            'accept' => $easemob_username
        ]);
        $return = 0;
        foreach($msg_data as $k => $v) {
            if($v['status'] == '0') { $return++; }
        }
        AppIntModel::jsonReturn($return);
    }
    //根据订单id获取指定订单未读消息数量
    public function UnreadMessagesOnly() {
        $msg_data = AppIntModel::getSelect('yyb_webim_msg',[
            'dialog_id' => $_POST['con_id']
        ]);
        $return = 0;
        foreach($msg_data as $k => $v) {
            if($v['status'] == '0') { $return++; }
        }
        AppIntModel::jsonReturn($return);
    }
    /**
     * APP医生端消息中心
    **/
    //获取消息中心数据(获取指定类型数据)
    public function GetMsgCenterData() {
        $return = AppIntModel::getSelect('yyb_doctor_msgcenter',[
            'd_id' => $_POST['d_id'],
            'type' => $_POST['type']
        ]);
        AppIntModel::jsonReturn($return);
    }
    //获取消息中心数据(获取全部数据)
    public function GetMsgCenterData_All() {
        $return = AppIntModel::getSelect('yyb_doctor_msgcenter',[
            'd_id' => $_POST['d_id']
        ]);
        if($return==''){$return=0;}
        AppIntModel::jsonReturn($return);
    }
    //新增消息中心信息数据(咨询信息)
    public function AddMsgCenterData($c_id,$u_id,$d_id,$state) {
        //获取订单数据
        $C_Data = $this -> getcdata3($c_id);
        //获取医生数据
        $DoctorData = $this -> yyb_doctor_Return($d_id);
        //获取用户数据
        $UserData = $this -> yyb_user2($u_id);
        //获取订单类型
        $type = $C_Data[0]['type'];
        //获取医生名称和用户名称
        $DoctorName = $DoctorData[0]['nick_name'];
        $UserName = $UserData[0]['nick_name'];
        //信息拼接
        $href = 'http://app.uyihui.cn/yybdoctor/html/Consult.html?doctorid='.$d_id.'&userid='.$u_id.'&id='.$c_id;
        if($state=='咨询开始') {
            $value = $UserName.'向你发起了'.$type;
        }
        if($state=='咨询结束') {
            $value = $UserName.'与你的'.$type.'已经结束,点击此处可对其进行回访~';
        }
        $return = AppIntModel::AddData('yyb_doctor_msgcenter',[],[
            'd_id' => $d_id,
            'value' => $value,
            'href' => $href,
            'type' => '咨询信息',
            'state' => 0,
            'add_time' => time(),
        ]);
        if($return) {
            AppIntModel::jsonReturn([
                'data' => null,
                'error' => 0,
                'msg' => "成功",
                'msg_type' => 0
            ]);
        } else {
            AppIntModel::jsonReturn([
                'data' => null,
                'error' => 1,
                'msg' => "错误",
                'msg_type' => 0
            ]);
        }
    }
    //新增消息中心消息数据(互动信息,暂无)
    
    //新增消息中心消息数据(公告信息)
    
    //消息中心消息数据修改阅读状态(根据返回的消息id)
    public function MsgCenterData_state_alter() {
        AppIntModel::UpData('yyb_doctor_msgcenter',[ 
            'id'=>$_POST['id']
        ],[
            'state' => 1,
        ]);
        AppIntModel::jsonReturn(1);
    }
    //消息中心消息数据修改阅读状态(一键已读)
    public function MsgCenterData_state_alter2() {
        AppIntModel::UpData('yyb_doctor_msgcenter',[
            'd_id' => $_POST['d_id'],
            'type' => $_POST['type']
        ],[
            'state' => 1,
        ]);
        AppIntModel::jsonReturn(1);
    }
    //删除消息中心消息数据(根据返回的消息id)
    public function DeleteMsgCenterData() {
        AppIntModel::DeleteData('yyb_doctor_msgcenter',['id' => $_POST['id']]);
        AppIntModel::jsonReturn(1);
    }
    //删除消息中心消息数据(一键清空,批量删除)
    public function DeleteMsgCenterData_All() {
        AppIntModel::DeleteData('yyb_doctor_msgcenter',[
            'd_id' => $_POST['d_id'],
            'type' => $_POST['type']
        ]);
        AppIntModel::jsonReturn(1);
    }
    //设置提现密码
    public function Add_wd_password() {
        $Return = AppIntModel::UpData('yyb_doctor',[
            'doctor_id' => $_POST['DoctorID']
        ],[
            'wd_password' => md5($_POST['password'])
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //保存提现信息
    public function Add_yyb_doctor_destoon() {
        //验证提现密码
        $DoctorData = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['data']['doctor_id'],
        ]);
        $wd_password = $DoctorData[0]['wd_password'];
        if($wd_password == md5($_POST['data']['wd_password'])) {
            if(intval($DoctorData[0]['money']) <= 0) { AppIntModel::jsonReturn('提现金额最少为1元'); return; }
            //获取提现后的剩余余额
            $money_2 = intval($DoctorData[0]['money']) - intval($_POST['data']['money_number']);
            if($money_2 < 0) { AppIntModel::jsonReturn('提现金额超出可提现余额'); return; }
            //保存进医生提现申请表
            $Return = AppIntModel::AddData('yyb_doctor_destoon',[],[
                'doctor_id' => $_POST['data']['doctor_id'],
                'bank_card' => $_POST['data']['bank_card'],
                'back_name' => $_POST['data']['bank_name'],
                'money' => $_POST['data']['money_number'],
                'money_2' => $money_2,
                'time' => time(),
            ]);
            //保存进医生余额收支记录表
            $Return2 = AppIntModel::AddData('yyb_doctor_balance_record',[],[
                'doctor_id' => $_POST['data']['doctor_id'],
                'type' => '余额提现',
                'number' => $_POST['data']['money_number'],
                'current_balance' => $money_2,
                'add_time' => time(),
            ]);
            //保存进消息中心表
            $Return3 = AppIntModel::AddData('yyb_doctor_msgcenter',[],[
                'd_id' => $_POST['data']['doctor_id'],
                'value' => '提交提现申请成功,24小时内将会有专人处理',
                'href' => 'http://app.uyihui.cn/yybdoctor/html/mypurse/WithdrawalState.html?id='.$Return,
                'type' => '提现信息',
                'state' => 0,
                'add_time' => time(),
            ]);
            //修改用户余额
            AppIntModel::UpData('yyb_doctor',[
                'doctor_id' => $_POST['data']['doctor_id'],
            ],[
                'money' => $money_2,
            ]);
            AppIntModel::jsonReturn($Return);
        } else {
            AppIntModel::jsonReturn('提现密码错误');
        }
    }
    //获取医生提现信息(通过id)
    public function Get_destoon() {
        $Return = AppIntModel::getSelect('yyb_doctor_destoon',[
            'id' => $_POST['id']
        ]);
        AppIntModel::jsonReturn($Return[0]);
    }
    //医生端 我的患者模块 及 我的粉丝模块 数据接口 
    //我的患者列表数据
    public function MyPatientsList() {
        $c_data = AppIntModel::getSelect('yyb_consultation',[
            'd_id' => $_POST['d_id'],
        ]);
        $Return = [];
        $length = count($c_data);
        for($i=0; $i<$length; $i++) {
            $user_data = AppIntModel::getSelect('yyb_user',[
                'user_id' => $c_data[$i]['c_id']
            ]);
            if($user_data==[]) { AppIntModel::jsonReturn([]); exit; }
            array_push($Return,[
                'user_id' => $user_data[0]['user_id'],
                'nick_name' => $user_data[0]['nick_name'],
                'real_name' => $user_data[0]['real_name'],
                'avatar' => $user_data[0]['avatar'],
                'initial' => AppIntModel::getFirstCharter($user_data[0]['nick_name']),
            ]);
        }
        AppIntModel::jsonReturn($Return);
    }
    //用户详情数据
    public function UserDetails() {
        $user_data = AppIntModel::getSelect('yyb_user',[
            'user_id' => $_POST['user_id'],
        ]);
        $h_data = AppIntModel::getSelect('yyb_hr',[
            'user_id' => $_POST['user_id'],
        ]);
        $c_data = AppIntModel::getSelect('yyb_consultation',[
            'c_id' => $_POST['user_id'],
            'd_id' => $_POST['d_id'],
        ]);
        AppIntModel::jsonReturn([$user_data[0],$h_data[0],$c_data[0]]);
    }
    /**
    **患者评价
    **/
    //医生端获取患者评价数据
    public function Patientevaluation() {
        //根据医生id获取他的所有订单
        $impression_data = AppIntModel::getSelect('yyb_impression',[
            'doctor_id' => $_POST['DoctorID']
        ]);
        $Return = [];
        foreach ($impression_data as $key => $value) {
            //AppIntModel::jsonReturn($value);
            $Return[$key]['option'] = explode(',',$value['option']); //评价标签,字符串分割成数组
            $Return[$key]['level'] = $value['level']; //评价等级
            $Return[$key]['text'] = $value['text']; //评价内容
            $Return[$key]['add_time'] = date("Y-m-d",$value['add_time']); //评价时间
            //用户信息
            //通过id获取用户数据
            $UserData = $this -> yyb_user2($value['user_id']);
            //用户头像数据
            $Return[$key]['avatar'] = $UserData[0]['avatar'];
            //用户姓名数据
            $Return[$key]['userName'] = $UserData[0]['nick_name'];
        }
        AppIntModel::jsonReturn($Return);
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
    /**
     * 环信操作
     * */
    //向指定用户发送文本信息
    public function Easemob_text_push() {
        $easemobAccount = [];
        $options['client_id']='YXA6n2EzUKfcEeakoZ90MejBhw';
        $options['client_secret']='YXA6_60uIDrWt6zGjrYXrFx3wQFm_vY';
        $options['org_name']='1161161111115389';
        $options['app_name']='yyb';
        $easeMob = new Easemob($options);
        $easeMob -> sendText($_POST['d_id'],'users',[$_POST['u_id']],$_POST['value'],'ext');
        AppIntModel::jsonReturn(1);
    }
}
?>