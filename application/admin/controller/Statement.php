<?php
//APP声明、帮助编辑控制器
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
use app\admin\model\StatementModel;
use app\admin\model\QnimgurlModel;
use app\admin\model\ZhawentitleModel;
header("Content-type: text/html; charset=utf-8");
class Statement extends Base {
    public function index() {
        $model = new StatementModel;
        $data = $model -> getSelect([]);
        $title = "APP声明文件列表";
        $this -> assign('data',$data);
        $this -> assign('title',$title);
        return $this -> fetch('statement/index');
    }

    public function index0() {
        $model = new StatementModel;
        //获取默认数据
        $data = $model -> getSelect(['type'=>'APP声明']);
        $data[0]["content"] = (string)$data[0]["content"];
        //标题
        $title = "APP声明";
        //切换控件数据
        $option = [];
        $option[0] = "APP声明";
        $this -> assign('data',$data);
        $this -> assign('option',$option);
        $this -> assign('title',$title);
        return $this -> fetch('statement/editor');
    }

    public function index1() {
        $model = new StatementModel;
        $data = $model -> getSelect(['type'=>'APP帮助']);
        $title = "APP帮助";
        $option = [];
        $option[0] = "APP帮助";
        $this -> assign('data',$data);
        $this -> assign('option',$option);
        $this -> assign('title',$title);
        return $this -> fetch('statement/editor');
    }

    public function index2() {
        $model = new StatementModel;
        $data = $model -> getSelect(['type'=>'APP用户协议']);
        $title = "APP用户协议";
        $option = [];
        $option[0] = "APP用户协议";
        $this -> assign('data',$data);
        $this -> assign('option',$option);
        $this -> assign('title',$title);
        return $this -> fetch('statement/editor');
    }

    public function getData() {
        $model = new StatementModel;
        $where = [];
        $where['type'] = $_POST['type'];
        $data = [];
        $data['content'] = $_POST['content'];
        $data['title'] = $_POST['title'];
        //更新数据库数据
        $UpData = $model -> AlterData($where,$data);
    }   
    //显示所有杂文数据
    public function zhawen() {
        $model2 = new ZhawentitleModel;
        //获取标题数据
        $titleData =  $model2 -> AllData([]);
        // 获取数据集记录数
        $AllData = AppIntModel::AllData('yyb_app_zhawen',[]);
        $count = count($AllData);
        $zhawendata = AppIntModel::pagintate('yyb_app_zhawen',[],10,[]);
        $alr_url = [];
        $titlename = [];
        //循环数组
        foreach($zhawendata as $k => $v) {
            $alr_url[$k] = 'zhawen2?id='.$v['id'];
            $titlename[$k] = $model2 -> getSelect(['id'=>$v['title']]);
        }
        // echo '<pre>';
        // print_r($titlename);
        // exit;
        $title = "杂文标题管理";
        $title2 = "杂文数据管理";
        $this -> assign('count',$count);
        $this -> assign('titleData',$titleData);
        $this -> assign('zhawendata',$zhawendata);
        $this -> assign('title',$title);
        $this -> assign('title2',$title2);
        $this -> assign('alr_url',$alr_url);
        $this -> assign('titlename',$titlename);
        return $this -> fetch('statement/zhawen');
    }
    //修改杂文
    public function zhawen2() {
        $model2 = new ZhawentitleModel;
        $id = $_GET['id'];
        $where = [
            'id' => $id
        ];
        $return = AppIntModel::getSelect('yyb_app_zhawen',$where);
        $title = '杂文修改';
        $this -> assign('title',$title);
        $this -> assign('return',$return);
        return $this -> fetch('statement/zhawen2');
    }
    //增加杂文
    public function zhawenadd() {
        $title = '杂文增加';
        $this -> assign('title',$title);
        return $this -> fetch('statement/zhawenadd');
    }
    //添加杂文数据api
    public function addApi() {
        if($_POST['oper']=='新增') {
            $data = [
                'type'=>$_POST['type'],
                'article'=>$_POST['title'],
                'title'=>$_POST['title2'],
                'content'=>$_POST['content'],
                'update_time'=>date("Y-m-d H:i:s", time())
            ];
            $return = AppIntModel::AddData('yyb_app_zhawen',[],$data);
            echo $return;
        } else if($_POST['oper']=='修改') {
            $where = [
                'id'=>$_POST['id']
            ];
            $data = [
                'type'=>$_POST['type'],
                'article'=>$_POST['title'],
                'title'=>$_POST['title2'],
                'content'=>$_POST['content'],
                'update_time'=>date("Y-m-d H:i:s", time())
            ];
            $return = AppIntModel::UpData('yyb_app_zhawen',$where,$data);
            echo $return;
        }
    }
    //删除杂文api
    public function deleteApi() {
        $id = $_POST['id'];
        $where = [
            'id' => $id
        ];
        AppIntModel::DeleteData($where);
        echo '删除成功';
    }
    public function ueditor() {
        //ueditor图片上传
        $file = $_FILES['upfile'];
        if ($_FILES["upfile"]["error"] == 0) {
            //上传文件到七牛CDN
            $filePath = $file['tmp_name'];
            $ImgName = $file["name"];
            $QnUpData = $this -> QnUpData($filePath,$ImgName);
            //放进七牛云图片链接数据库
            $model = new QnimgurlModel();
            $where = [];
            $where['qn_img_url'] = $QnUpData['key'];
            $where['explain'] = "statement";
            $data = $model -> AddData($where);
            $res = array(
                "state" => "SUCCESS",  //上传状态，上传成功时必须返回"SUCCESS"
                "url"   => "http://cdn.uyihui.cn/".$QnUpData['key'], //CDN地址
            );
            echo json_encode($res);
        }
    }

    public function QnUpData($filePath,$ImgName) {
        //图片直传七牛云服务端
        $accessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $secretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $bucket = 'video1';
        //生成Token
        $token = $this -> QnToken($accessKey,$secretKey,$bucket);
        //要上传文件的本地路径
        $filePath = $filePath;
        //上传到七牛后保存的文件名
        //$key = 'test' . date('YmdHis') . rand(0, 9999) . '.jpg';
        $key = $ImgName;
        //上传
        $QnReturn = $this -> QnUpFile($token,$key,$filePath);
        return $QnReturn;
    }   
}
?>