<?php
//APP下载链接设置
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
class Appdownloadlink extends Base {
    // APP下载链接设置
    public function index() {
        $title = "优孕宝APP下载链接设置";
        $this -> assign('title',$title);
        // 获取下载链接数据
        $data['urldata'] = AppIntModel::getSelect('yyb_appdownloadlink',[]);
        $this -> assign('urldata',$data['urldata']);
        return $this -> fetch('appdownloadlink/index');
    }  
}
?> 