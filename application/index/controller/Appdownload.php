<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\admin\model\AppIntModel;
class Appdownload extends Controller {
    public function index() {
        $title = '优孕宝APP下载';
        $this -> assign('title',$title);
        return $this -> fetch('appdownload/index');
    }
}
?>