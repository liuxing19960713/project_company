<?php
//app用户端健康档案数据接口
namespace app\appapi\controller;
use think\Controller;
use think\Db;
use app\appapi\model\AppIntModel;
use app\appapi\controller\Wsapi;
// 七牛云
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");
class Apphr extends Controller {
    /* 健康档案 */ 
    //健康档案数据初始化
    public function Dataopen() {
        $Hrdata = AppIntModel::getSelect('yyb_hr',[
            'user_id' => $_POST['user_id'],
        ]);
        //AppIntModel::jsonReturn($Hrdata);
        if($Hrdata) {
            //数据已经存在
            AppIntModel::jsonReturn('0');
        } else {
            //数据不存在
            AppIntModel::AddData('yyb_hr',[],[
                'user_id' => $_POST['user_id'],
                'add_time' => time(),
            ]);
            AppIntModel::jsonReturn('1');
        }
    }
    //健康档案数据保存
    public function Hrdatastorage() {
        AppIntModel::UpData('yyb_hr',[
            'user_id' => $_POST['user_id']
        ],[
            $_POST['dataname'] => $_POST['val']
        ]);
    }
    //健康档案数据获取
    public function Hrdatastorage2() {
        AppIntModel::Alldata('yyb_hr',[
            'user_id' => $_POST['user_id']
        ],[]);
    }
    //健康档案_检查情况数据保存
    public function inspectiondatastorage() {
        $i = count($_POST['Imgdata']);
        $return = AppIntModel::AddData('yyb_hr_inspection',[],[
            'user_id' => $_POST['user_id'],
            'item' => $_POST['item'],
            'hospital' => $_POST['hospital'],
            'examination_date' => $_POST['examination_date'],
            'result' => $_POST['result'],
            'Imgdata' => json_encode($_POST['Imgdata']),
            'picture_note' => $_POST['picture_note'],
            'add_time' => time()
        ]);
        AppIntModel::jsonReturn($return);
    }
    //健康档案_既往诊断数据保存
    public function priordiagnosisdatastorage() {
        $i = count($_POST['Imgdata']);
        $return = AppIntModel::AddData('yyb_hr_priordiagnosis',[],[
            'user_id' => $_POST['user_id'],
            'hospital' => $_POST['hospital'],
            'examination_date' => $_POST['examination_date'],
            'symptoms_described' => $_POST['symptoms_described'],
            'result' => $_POST['result'],
            'Imgdata' => json_encode($_POST['Imgdata']),
            'picture_note' => $_POST['picture_note'],
            'add_time' => time(),
        ]);
        AppIntModel::jsonReturn($return);
    }
    //健康档案_排卵情况数据保存
    public function ovulationdatastorage() {
        $i = count($_POST['Imgdata']);
        $return = AppIntModel::AddData('yyb_hr_ovulation',[],[
            'user_id' => $_POST['user_id'],
            'hospital' => $_POST['hospital'],
            'examination_date' => $_POST['examination_date'],
            'left_follicle' => $_POST['left_follicle'],
            'right_follicle' => $_POST['right_follicle'],
            'Intima_thickness' => $_POST['Intima_thickness'],
            'ovulate' => $_POST['ovulate'],
            'notes' => $_POST['picture_note'],
            'imgdata' => json_encode($_POST['Imgdata']),
            'add_time' => time(),
        ]);
        AppIntModel::jsonReturn($return);
    }
    //检查情况、既往诊断、排卵情况数据获取(通过用户id)
    public function inspectiondatagain() {
        //基本信息数据
        $basicinfoData = AppIntModel::getSelect('yyb_hr',[
            'user_id' => $_POST['user_id']
        ]);
        //检查情况数据
        $inspectionData = AppIntModel::getSelect('yyb_hr_inspection',[
            'user_id' => $_POST['user_id']
        ]);
        //既往诊断数据
        $priordiagnosisData = AppIntModel::getSelect('yyb_hr_priordiagnosis',[
            'user_id' => $_POST['user_id']
        ]);
        //排卵情况数据
        $ovulationData = AppIntModel::getSelect('yyb_hr_ovulation',[
            'user_id' => $_POST['user_id']
        ]);
        AppIntModel::jsonReturn([
            'basicinfoData'=>$basicinfoData,
            'inspectionData'=>$inspectionData,
            'priordiagnosisData'=>$priordiagnosisData,
            'ovulationData'=>$ovulationData,
        ]);
    }
    //通过id获取数据
    public function Datagain() {
        $table = $_POST['table'];
        $return = AppIntModel::getSelect($table,[
            'id' => $_POST['id'],
        ]);
        AppIntModel::jsonReturn($return);
    }
    //通过id删除对应 检查情况、既往诊断、排卵情况数据
    public function DeleteData() {
        $id = $_POST['id'];
        $table = $_POST['table'];
        $imgdata = $_POST['imgdata'];
        $class = $_POST['class'];
        //图片数据状态修改为删除
        $length = count($imgdata);
        for($i=0; $i<$length; $i++) {
            AppIntModel::UpData('yyb_hr_imgurl',[
                'class' => $class,
                'imgdata' => $imgdata[$i]
            ],[
                'delete' => 1
            ]);
        }
        //删除记录 
        $return = AppIntModel::DeleteData($table,[
            'id' => $id
        ]);
        AppIntModel::jsonReturn($return);
    }
    //生成七牛上传token
    public function QnToken() {
        $upManager = new UploadManager();
        $accessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $secretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        //创建auth对象
        $auth = new Auth($accessKey,$secretKey);
        $bucket = 'video1'; //上传的空间名
        //使用auth对象中的方法传入空间名参数来新建一个上传Token
        $upToken = $auth -> uploadToken($bucket);
        AppIntModel::jsonReturn($upToken);
    }
    //保存图片数据
    public function Imgdata() {
        $return = AppIntModel::AddData('yyb_hr_imgurl',[],[
            'imgdata' => $_POST['imgdata'],
            'type' => $_POST['type'],
            'user_id' => $_POST['user_id'],
            'class' => $_POST['class'],
            'add_time' => time(),
        ]);
        AppIntModel::jsonReturn($return);
    }
    //删除七牛图片数据
    public function deleteimgdata() {
        $return = AppIntModel::UpData('yyb_hr_imgurl',[
            'imgdata' => $_POST['imgdata'],
            'type' => $_POST['type'],
            'class' => $_POST['class'],
            'user_id' => $_POST['user_id'],
        ],[
            'delete' => 1,
        ]);
        AppIntModel::jsonReturn($return);
    }
}
?>