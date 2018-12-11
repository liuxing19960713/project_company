<?php
//app后台管理公用数据接口
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
//use app\appapi\controller\Wsapi;
//环信接口: 
use app\common\tools\Easemob;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");
class Adminapi extends Controller {
    // 优孕宝APP下载链接设置数据接口
    public function appdownloadlink_alter() {
        //修改操作
        if($_POST['operator']=='alter') {
            AppIntModel::UpData('yyb_appdownloadlink',['type'=>'Android'],['url'=>$_POST['Android_url']]);
            AppIntModel::UpData('yyb_appdownloadlink',['type'=>'IOS'],['url'=>$_POST['Ios_url']]);
            AppIntModel::jsonReturn(1);
        }
        //获取数据
        if($_POST['operator']=='get_data') {
            $ReturnData = AppIntModel::getSelect('yyb_appdownloadlink',[]);
            AppIntModel::jsonReturn($ReturnData);
        }
    }

}
?>