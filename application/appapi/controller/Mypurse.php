<?php
/**
 * App我的钱包控制器
**/ 
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
use app\appapi\controller\Wsapi;
//环信接口: 
use app\common\tools\Easemob;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");
class Mypurse extends Controller {
    //余额,累计收入,累计服务次数数据
    public function index() {
        $Return = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['d_id']
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //获取余额明细数据
    public function balance() {
        $Return = AppIntModel::getSelect('yyb_doctor_balance_record',[
            'doctor_id' => $_POST['doctor_id']
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //获取银行卡数据
    public function GetBankCard() {
        $Return = AppIntModel::getSelect('yyb_card',[
            'doctor_id' => $_POST['doctor_id']
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //添加银行卡数据
    public function BankCard() {
        $DoctorData = AppIntModel::getSelect('yyb_doctor',[
            'doctor_id' => $_POST['doctor_id']
        ]);
        //姓名验证
        if($DoctorData[0]['nick_name']!=$_POST['doctor_name']) {
            AppIntModel::jsonReturn('姓名不一致');
            return;
        }
        //银行卡数量验证
        $BankCardInt = AppIntModel::getSelect('yyb_card',[
            'doctor_id' => $_POST['doctor_id']
        ]);
        if(count($BankCardInt) > 4) {
            AppIntModel::jsonReturn('最多只能绑定5张银行卡');
            return;
        }
        //验证银行卡是否已存在
        $BankCardData = AppIntModel::getSelect('yyb_card',[
            'card_number' => $_POST['card_number']
        ]);
        if($BankCardData) {
            AppIntModel::jsonReturn('该银行卡已存在');
            return;
        }
        //银行卡数据入库
        $card_number = intval($_POST['card_number']);
        $Return = AppIntModel::AddData('yyb_card',[],[
            'card_number' => $card_number,
            'card_type' => $_POST['card_type'],
            'bank' => $_POST['bank'],
            'doctor_name' => $_POST['doctor_name'],
            'doctor_id' => $_POST['doctor_id'],
            'add_time' => time(),
        ]);
        if($Return) {AppIntModel::jsonReturn($Return);}
    }
    //删除指定银行卡数据(通过id)
    public function DeleteBankCard() { 
        $Return = AppIntModel::DeleteData('yyb_card',[
            'id' => $_POST['id'],
            'doctor_id' => $_POST['doctor_id'],
        ]);
        AppIntModel::jsonReturn($Return);
    }
    //处理医生提现请求
    public function Doctor_destoon() {
        
    }

}
?>