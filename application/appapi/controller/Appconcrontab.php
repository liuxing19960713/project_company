<?php
/**
 * app定时任务查询接口 
 * crontab -e
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
class Appconcrontab extends Controller {
    //服务器定时任务测试接口
    public function test() {
        $data = AppIntModel::getSelect('test',[
            'id' => 1
        ]);
        $data = intval($data[0]['test'])+1;
        AppIntModel::UpData('test',[
            'id' => 1
        ],[
            'test' => $data
        ]);
    }
    //订单自动退款接口(超过24小时自动退款)
    public function Autorefund() {
        $data1 = AppIntModel::getSelect('yyb_consultation',[
            //退款状态（判断是否已退款）
            'is_refunded' => 'no',
            //医生是否已回复
            'doctor_responded' => '0',
            //支付订单号（判断是否已支付）
            'trade_no' => ['neq','']
        ]);
        $length = count($data1);
        $time = time();
        $or_id = [];
        //判断是否已经超过24小时
        for($k=0; $k<$length; $k++) {
            $create_time = $data1[$k]['create_time'];
            $ct = $time - $create_time;
            if($ct >= 86400) {
                array_push($or_id,$data1[$k]['trade_no']);
            }
        }
        //根据支付订单号获取对应金额，并向第三方退款接口发送post请求
        $length = count($or_id);
        for($k=0; $k<$length; $k++) {
            $data2 = AppIntModel::getSelect('yyb_order',[
                'bill_no' => $or_id[$k] 
            ]);
            $money = $data2[0]['money'];
            //微信退款
            if($data2[0]['pay_type']=='wxpay') {
                $url1 = 'http://api.uyihui.cn/api/WxpayAPI/example/refund.php';
                $return = [
                    'out_trade_no' => $or_id[$k],
                    'total_fee' => $money*100,
                    'refund_fee' => $money*100
                ];
                print_r($return);
                AppIntModel::curl_post_https($url1,$return);
            }
            //支付宝退款
            if($data2[0]['pay_type']=='alipay') {
                $url2 = 'http://api.uyihui.cn/api/alipay/wappay/refund.php';
                $return = [
                    'WIDout_trade_no' => $or_id[$k],
                    'WIDrefund_amount' => $money
                ];
                print_r($return);
                AppIntModel::curl_post_https($url2,$return);
            }
        }
    }
    //定时任务修改订单状态接口(医生第一次回复后的24小时)
    public function Automos() {
        $data1 = AppIntModel::getSelect('yyb_consultation',[
            'state' => '正在咨询'
        ]);
        $length = count($data1);
        $time = time();
        for($k=0; $k<$length; $k++) {
            //判断是否快速咨询
            if($data1[$k]['Quick']==1) {
                //快速咨询根据对话生成时间计算24小时
                $t = $data1[$k]['create_time'] + 86400;
            } else {
                //普通图文咨询根据初始聊天时间来计算24小时
                if($data1[$k]['Initial_chat_time']!=0) {$t = $data1[$k]['Initial_chat_time'] + 86400;}
            }
            $ct = $t - $time;
            //判断是否已经超过24小时
            if($ct <= 0) {
                //修改订单状态
                $return = AppIntModel::UpData('yyb_consultation',[
                    'con_id' => $data1[$k]['con_id']
                ],[
                    'state' => '已结束'
                ]);
                AppIntModel::jsonReturn($return);
            }
        }
    }
    //判定匹配中的快速咨询是否过期，过期则将订单转到固定接收账号
    public function Autoquick() {
        $data1 = AppIntModel::getSelect('yyb_consultation',[
            'd_id' => 0,
            'state' => '匹配中',
            'Quick' => '1',
        ]);
        $length = count($data1);
        $time = time();
        //判断是否已经超过3分钟
        for($k=0; $k<$length; $k++) {
            $Initial_chat_time = $data1[$k]['create_time'] + 180;
            $ct = $Initial_chat_time - $time;
            if($ct <= 0) {
                //修改绑定状态
                $data = [
                    'con_id' => $data1[$k]['con_id'],
                    'd_id' => $data1[$k]['doctor_match'],
                    'status' => '2'
                ];
                AppIntModel::jsonReturn($data);
                AppIntModel::curl_post_https('http://api.uyihui.cn/api/api.php?app=consultative&act=Binding',$data);
                //使用环信接口给用户发送医生已接单的提醒
                $options['client_id']='YXA6n2EzUKfcEeakoZ90MejBhw';
                $options['client_secret']='YXA6_60uIDrWt6zGjrYXrFx3wQFm_vY';
                $options['org_name']='1161161111115389';
                $options['app_name']='yyb';
                $easeMob = new Easemob($options);
                //获取医生环信账号
                $data2 = AppIntModel::getSelect('yyb_doctor',['doctor_id' => $data1[$k]['doctor_match']]);
                $doctor_easemob_username = $data2[0]['doctor_id'];
                //获取用户环信账号
                $data2 = AppIntModel::getSelect('yyb_user',['user_id' => $data1[$k]['c_id']]);
                $user_easemob_username = $data2[0]['user_id'];
                $easeMob -> sendText($doctor_easemob_username,'users',[$user_easemob_username],'医生已接单','ext');
            }
        }
    }
    //每日晚上0时，修改用户表签到字段为未签到
    public function Usercheck() {
        AppIntModel::UpData('yyb_user','1=1',[
            'checks' => 'no'
        ]);
    }
}
?>