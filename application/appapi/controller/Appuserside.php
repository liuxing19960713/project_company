<?php
//app对用户端数据接口
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
//环信接口: 
use app\common\tools\Easemob;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");
class Appuserside extends Controller {
    //外部解析医生邀请码
    public function code_conversion() {
        $code = strtoupper($_POST['code']);
        $Phone = AppIntModel::decode($code);
        $DoctorData = AppIntModel::getSelect('yyb_doctor',['mobile' => $Phone]);
        if($DoctorData) {
            $Return = [
                'data' => $DoctorData[0]['doctor_id'].'',
                'error' => "0",
                'msg' => "成功",
                'msg_type' => "0",
            ];
        } else {
            $Return = [
                'data' => null,
                'error' => "1",
                'msg' => "邀请码不正确",
                'msg_type' => "0",
            ];
        }
        AppIntModel::jsonReturn($Return);
    }
    //获取优孕宝隐私保护指引
    public function Privacyguidelines() {
        $Return = AppIntModel::getSelect('yyb_app_statement',[
            'id' => 4
        ]);
        AppIntModel::jsonReturn($Return);
    }
}
?>