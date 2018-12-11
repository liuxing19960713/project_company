<?php
//app快速咨询数据接口
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
use app\appapi\controller\Wsapi;
//环信接口: 
use app\common\tools\Easemob;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");
class Appconquick extends Controller {
    //查询是否存在快速咨询数据
    public function index() {
        $cdata = AppIntModel::getSelect("yyb_consultation",[
            'd_id' => 0,
            'state' => '匹配中',
            'Quick' => '1',
            'doctor_match' => $_POST['doctor_id']
        ]);
        if($cdata) {
            $c_id = $cdata[0]['c_id'];
            $cdata2 = AppIntModel::getSelect("yyb_consultation",[
                'c_id' => $c_id,
                'd_id' => $_POST['doctor_id']
            ]);
            $cdata[0]['con_id'] = ($cdata2)?($cdata2[0]['con_id']):($cdata[0]['con_id']);
            AppIntModel::jsonReturn($cdata);
        } else {
            AppIntModel::jsonReturn(0);
        }
    }
}
?>