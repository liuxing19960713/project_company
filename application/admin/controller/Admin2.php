<?php
//后台管理系统外部模块登录验证类
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
header('Access-Control-Allow-Origin: *');
class admin2 {
    //验证登录状态
    public function login_verify() {
        AppIntModel::jsonpReturn($_GET['Longin_Verify'],true);
    }
    /**
     * WebIM(环信)消息管理模块
    **/
    //数据获取
    public function webim_msg_get_data() {
        $return = AppIntModel::pagintate('yyb_webim_msg',[],10,[
            'page' => $_GET['page']//当前分页页数
        ]);
        AppIntModel::jsonpReturn($_GET['get_msg_data'],$return);
    }
    /**
     * APP医生标签数据管理
     * */
    //数据获取
    public function doctor_label_get_data() {
        $return = AppIntModel::AllData('yyb_doctor_label',[]);
        AppIntModel::jsonpReturn($_GET['doctor_label_get_data'],$return);
    }
    //数据添加
    public function doctor_label_add_data() {
        $return = AppIntModel::AddData('yyb_doctor_label',[],[
            'name' => $_GET['name'],
            'last_time' => time(),
        ]);
        AppIntModel::jsonpReturn($_GET['doctor_label_add_data'],$return);
    }
    //数据更新
    public function doctor_label_up_data() {
        $return = AppIntModel::UpData('yyb_doctor_label',[
            'id' => $_GET['id']
        ],[
            'name' => $_GET['name']
        ]);
        AppIntModel::jsonpReturn($_GET['doctor_label_up_data'],$return);
    }
    /**
     * 医生提现申请管理模块
    **/
    //医生提现数据获取
    public function doctor_dastoon_get_data() {
        $return = AppIntModel::pagintate('yyb_doctor_destoon',[],10,[
            'page' => $_GET['page']//当前分页页数
        ]);
        AppIntModel::jsonpReturn($_GET['get_doctor_destoon_data'],$return);
    }
    //提现申请审核申请
    public function doctor_dastoon_dispose() {
        $id = $_POST['wd_id'];
        $doctor_id = $_POST['doctor_id'];
        $type = $_POST['type'];
        //获取提现数据 表：yyb_doctor_destoon
        $DestoonData = AppIntModel::getSelect('yyb_doctor_destoon',['id' => $id]);
        //获取医生数据 表：yyb_doctor
        $DoctorData = AppIntModel::getSelect('yyb_doctor',['doctor_id' => $doctor_id]);
        //提现申请通过
        if($type == 1) {
            //1.处理钱包数据 表：yyb_doctor 
            //账户余额、累计收入数据更新 字段：money acc_income
            $returnA = AppIntModel::UpData('yyb_doctor',['doctor_id' => $doctor_id],[
                'money' => $DestoonData[0]['money_2'],
                'acc_income' => floatval($DoctorData[0]['acc_income']) + floatval($DestoonData[0]['money']),
            ]);
            //2.新增医生余额收支记录数据 表：yyb_doctor_balance_record
            $returnB = AppIntModel::AddData('yyb_doctor_balance_record',[],[
                'doctor_id' => $doctor_id,
                'type' => '余额提现',
                'number' => '-'.$DestoonData[0]['money'], //提现金额
                'current_balance' => $DestoonData[0]['money_2'], //当前余额
                'add_time' => time(),
            ]);
            //3.新增提现公告消息数据 表：yyb_doctor_msgcenter
            $returnC = AppIntModel::AddData('yyb_doctor_msgcenter',[],[
                'd_id' => $doctor_id,
                'value' => '成功提现'.$DestoonData[0]['money'].'元!',
                'href' => '#',
                'type' => '提现信息',
                'state' => 0,
                'add_time' => time(),
            ]);
            //4.修改提现数据状态 表：yyb_doctor_destoon
            $returnD = AppIntModel::UpData('yyb_doctor_destoon',['id' => $id],['state' => '审核成功']);
            //5.返回数据
            if($returnA&&$returnB&&$returnC&&$returnD) { 
                AppIntModel::success('OK'); 
            } else {
                AppIntModel::error('数据修改接口出现问题了~请排查'); 
            }
            exit;
        }
        //提现申请被拒绝
        if($type == 0) {
            //1.新增提现公告消息数据 表：yyb_doctor_msgcenter
            $returnA = AppIntModel::AddData('yyb_doctor_msgcenter',[],[
                'd_id' => $doctor_id,
                'value' => '不好意思~您发起的提现'.$DestoonData[0]['money'].'元的申请被管理员拒绝了哟~',
                'href' => '#',
                'type' => '提现信息',
                'state' => 0,
                'add_time' => time(),
            ]);
            $returnB = AppIntModel::UpData('yyb_doctor_destoon',['id' => $id],['state' => '审核失败']);
            //2.返回数据
            if($returnA&&$returnB) { 
                AppIntModel::success('OK'); 
            } else {
                AppIntModel::error('数据修改接口出现问题了~请排查'); 
            }
            exit;
        }
    }
}