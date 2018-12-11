<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/11/19
 * Time: 19:10
 */
namespace app\doctor\controller;

use app\doctor\model\Appointment;
use app\web\logic\ConsultationLogic;
use think\Controller;
use app\index\controller\Base;
use app\doctor\model\User as UserModel;
use app\doctor\model\Consultation as ConsultationModel;
use app\index\model\Chat as ChatModel;
use app\web\logic\CustomerLogic;
use app\web\logic\DoctorLogic;
use think\Db;
use app\common\model\Easemob;
use app\index\model\Message;
use app\doctor\model\Timeline as TimelineModel;
use app\index\model\Finance;
use think\Exception;

class Consultation extends Base
{
    public $UserModel;
    public $Consultation;
    public $CustomerLogic;
    public $ChatModel;
    public $Message;
    public $Appoint;
    public $TimelineModel;

    public function __construct()
    {
        parent::__construct();
        $this->UserModel = new UserModel();
        $this->Consultation = new ConsultationModel();
        $this->CustomerLogic = new CustomerLogic();
        $this->ChatModel = new ChatModel();
        $this->Message = new Message();
        $this->Appoint = new Appointment();
        $this->TimelineModel = new TimelineModel();
    }

    //获取 图文 问诊详情
    public function getConsultationInfo()
    {
        $token = get_token();
        $con_id = get_post_value('con_id');
        $cp_id = get_post_value('cp_id');

        if (empty($token) || (empty($con_id) && empty($cp_id))) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        $c_id = $this->CustomerLogic->valiSession($token);
        if (!$d_id && !$c_id) {
            return $this->private_result('10003');
        }

        $where = empty($d_id) ? " AND con.c_id = " . $c_id : " AND con.d_id = " . $d_id;

        if (!empty($con_id)) {
            $ConsultationInfo = $this->Consultation->getConsultationInfo($con_id, $where);
        } else {
            $ConsultationInfo = $this->Consultation->getConsultationInfoByCp($cp_id);
        }

        if ($ConsultationInfo === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        } else if (empty($ConsultationInfo)) {
            return $this->private_result("40001");
        } else {
            return $this->private_result("0001", $ConsultationInfo);
        }
    }

    //咨询管理
    public function manager()
    {
        $token = get_token();
        $page = empty($_POST['page']) ? 1 : $_POST['page'];
        $type = isset($_POST['type']) ? $_POST['type'] : "";
        if (empty($token) || empty($type)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $ConsultationInfo = $this->Consultation->getList($d_id,
            "con.state != '已完成' AND con.state != '已取消' AND con.type = '" . $type . "' AND ", $page);

        $appointInfo = array();
        if ($type == '电话咨询' || $type == '视频咨询') {
            if ($page == 1) {
                $appointResult = $this->Appoint->getList($d_id, $type);
            }
        }

        if (!empty($appointResult)) {
            $appointInfo = $appointResult;
        }

        if ($ConsultationInfo !== false) {
            return $this->private_result("0001", ['appointment' => $appointInfo, 'consultation' => $ConsultationInfo]);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //咨询历史
    public function history()
    {
        $token = get_token();
        $page = empty($_POST['page']) ? 1 : $_POST['page'];
        $num = empty($_POST['num']) ? 20 : $_POST['num'];
        $type = isset($_POST['type']) ? $_POST['type'] : "";
        if (empty($token) || empty($type)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $ConsultationInfo = $this->Consultation->getList2("con.d_id = " . $d_id,
            "con.state = '已完成' AND con.type = '" . $type . "' AND ", $page, $num);
        if ($ConsultationInfo !== false) {
            return $this->private_result("0001", $ConsultationInfo);

        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //上传问诊报告
    public function uploadReport()
    {
        $token = get_token();
        $con_id = empty($_POST['con_id']) ? "" : $_POST['con_id'];
        $diagnose = isset($_POST['diagnose']) ? $_POST['diagnose'] : "";//
        $advise = isset($_POST['advise']) ? $_POST['advise'] : "";
        if (empty($token) || empty($con_id) || empty($diagnose) || empty($advise)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $consultation = $this->Consultation->get("con_id = $con_id AND d_id = $d_id",
            "c_id,state,type,money,create_time,is_extend,total_time,valid_time,service_id");
        if (empty($consultation) || $consultation['state'] == '已取消') {//未开始问诊
            return $this->private_result("40001");
        }
        $report = $this->Consultation->getReport("con_id = $con_id AND d_id = $d_id", "con_id");
        if (!empty($report) || $consultation['state'] == '已完成') {//已上传问诊报告
            return $this->private_result("40005");
        }

        try {
            $customer = Db::name('customer')->where("c_id = " . $consultation['c_id'] . "")->field("c_id,nick_name,easemob_username")->find();
            $doctor = Db::name('doctor')->where("d_id = $d_id")->field("real_name,image_price,phone_price,video_price,guidance_price,private_price,price_percentage,ex_price")->find();
        } catch (\Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        //图文       医生图文价格 * 提成  doctor price * price_percentage
        if ($consultation['type'] == '图文咨询' && $consultation['service_id'] == 0) {
            if ($consultation['is_extend'] == 'yes') {//是否转诊
                $ex_doctor_money = $doctor['ex_price'];
            }
            $service_type = '图文咨询';
            $finance_type =  'image';
            $doctor_money = $doctor['image_price'] * $doctor['price_percentage'] / 100;
        } else if ($consultation['type'] == '图文咨询' && $consultation['service_id'] != 0) {
            //私人/院后 图文咨询且service_id不为空 判断时间是否已结束     service real_money * 提成
            $service = Db::name('service')->where('se_id = '. $consultation['service_id'])->find();
            if($service['type'] == '院后指导'){
                $finance_type = 'guidance';
            }else{
                $finance_type = 'private';
            }
            $service_type = $service['type'];
            $doctor_money = $service['real_money'] * $doctor['price_percentage'] / 100;
        }else if($consultation['type'] == '电话咨询' || $consultation['type'] == '视频咨询'){
            //电话/视频  判断时间是否已结束   医生电话/视频价格 * 服务时间（进1） * 提成
            if($consultation['type'] == '电话咨询'){
                $price = $doctor['phone_price'];
                $finance_type =  'phone';
            }else{
                $price = $doctor['video_price'];
                $finance_type =  'video';
            }
            $service_type = $consultation['type'];

            $doctor_money = ceil(($consultation['total_time']-$consultation['valid_time'])/60) * $price * $doctor['price_percentage']  / 100;
        }else{
            return $this->private_result("10005");
        }


        trans_start();
        $data['d_id'] = $d_id;
        $data['con_id'] = $con_id;
        $data['diagnose'] = $diagnose;
        $data['advise'] = $advise;
        $data['report_time'] = time();
        $is_add = $this->Consultation->addReport($data);
        $set = $this->Consultation->set("con_id = $con_id AND d_id = $d_id", array("state" => '已完成'));

        if(!empty($ex_doctor_money)){//转诊加钱
            $ex_consultation = Db::name('ex_consultation')->where("con_id = $con_id AND d_id = $d_id")->field("b_d_id")->find();
            $ex_update = Db::name('doctor')->where("d_id = ".$ex_consultation['b_d_id'])->setInc('money', $ex_doctor_money);
            //插入交易记录
            if($ex_doctor_money != 0){
            $extra = serialize(array("con_id" => $con_id, 'type' => $service_type));
            $finance = new Finance();
            $is_finance = $finance->insert($ex_consultation['b_d_id'], "doctor", $ex_doctor_money, 'extend', 'in', $extra, $data['report_time']);
                if ($is_finance === false) {
                    trans_rollback();
                    return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
                }
            }
            if ($ex_update === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
            }
        }

        $add_money = Db::name('doctor')->where("d_id = $d_id")->setInc("money",$doctor_money);
        //插入交易记录
        if($doctor_money != 0){
            $extra = serialize(array("con_id" => $con_id, 'type' => $service_type));
            $finance = new Finance();
            $is_finance = $finance->insert($d_id, "doctor", $doctor_money, $finance_type, 'in', $extra, $data['report_time']);
            if ($is_finance === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
            }
        }
        if ($add_money === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }


        // 咨询次数加1
        $doctorLogic = new DoctorLogic();
        $updateResult = $doctorLogic->addServiceTimes($d_id);
        if ($is_add === false || $set === false || $updateResult === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $easemob = new Easemob();
        $token = $easemob->getToken();

        //消息推送
        $message = $this->Message->insertMassage($customer['c_id'], "customer",
            $doctor['real_name'] . " 医生向您发来一份问诊报告，您对医生的服务是否满意？请不要忘记对其进行评价哦", '', '', "图文咨询", 'yes');//推送信息
        if (!$message) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        trans_commit();
        $message['show'] = 'yes';
        $easemob->pushSendChat($customer['easemob_username'],
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        return $this->private_result("0001", $customer);

    }

    //查看问诊报告
    public function getReport()
    {
        $token = get_token();
        $con_id = empty($_POST['con_id']) ? "" : $_POST['con_id'];
        if (empty($token) || empty($con_id)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        $c_id = $this->CustomerLogic->valiSession($token);
        if (!$d_id && !$c_id) {
            return $this->private_result('10003');
        }

        $where = empty($d_id) ? "con_id = $con_id AND c_id = $c_id" : "con_id = $con_id AND d_id = $d_id";
        $consultation = $this->Consultation->get($where, "con_id,c_id,d_id");
        if (empty($consultation)) { //未开始问诊

            $whereEx = "b_con_id = {$con_id} AND d_id = {$d_id}";
            $consultationEx = $this->Consultation->getEx($whereEx, 'b_con_id, b_d_id');

            if (empty($consultationEx)) {
                return $this->private_result("40001");

            }
            $report = $this->Consultation->getReport(array("con_id" => $consultation['b_con_id'], "d_id" => $consultation['b_d_id']));

        } else {
            $report = $this->Consultation->getReport(array("con_id" => $consultation['con_id'], "d_id" => $consultation['d_id']));
        }

        if ($report !== false) {
            return $this->private_result("0001", $report);
        } else {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

    }

    /**
     * 原属用户模块接口 暂时放医生模块 上传病历
     */
    public function uploadProfile()
    {
        $token = get_token();
        $con_id = empty($_POST['con_id']) ? "" : $_POST['con_id'];
        $data['name'] = isset($_POST['name']) ? $_POST['name'] : "";
        $data['age'] = isset($_POST['age']) ? $_POST['age'] : "";
        $data['gender'] = isset($_POST['gender']) ? $_POST['gender'] : "";
        $data['department'] = isset($_POST['department']) ? $_POST['department'] : "";
        $data['content'] = isset($_POST['content']) ? $_POST['content'] : "";
        if (empty($token) || empty($con_id) || empty($data['name']) || empty($data['age']) || empty($data['gender']) || empty($data['department']) || empty($data['content'])) {
            return $this->private_result("10001");
        }

        $c_id = $this->CustomerLogic->valiSession($token);
        if (!$c_id) {
            return $this->private_result('10003');
        }
        $consultation = $this->Consultation->get("con_id = $con_id AND c_id = $c_id", "con_id");
        if (empty($consultation)) {//未开始问诊
            return $this->private_result("40001");
        }
        $consultation = $this->Consultation->getProfile("con_id = $con_id AND c_id = $c_id", "con_id");
        if (!empty($consultation)) {//已上传病历
            return $this->private_result("40005");
        }
        $data['c_id'] = $c_id;
        $data['con_id'] = $con_id;
        $data = $this->getProfile($data);

        trans_start();
        $is_add = $this->Consultation->addProfile($data);
        $updateStatus = $this->Consultation->set(['con_id' => $con_id], ['state' => '进行中']);
        $easemob_username = $this->Consultation->getConsultationInfo($con_id);
        if (!empty($is_add) && $updateStatus !== false) {
            $send_data['con_id'] = $con_id;
            $send_data['name'] = $data['name'];
            $send_data['gender'] = $data['gender'];
            $send_data['age'] = $data['age'];
            $send_data['avatar'] = $easemob_username['avatar'];
            $send_data['desc'] = $data['content'];
            $chat_data['c_id'] = $easemob_username['c_id'];
            $chat_data['d_id'] = $easemob_username['d_id'];
            $chat_data['con_id'] = $easemob_username['con_id'];
            $chat_data['content'] = $data['content'];
            $chat_data['msg_type'] = "文字";
            $chat_data['create_time'] = time();
            $chat_data['chat_to'] = "c2d";
            $insert = $this->ChatModel->insert($chat_data);
            if (!$insert) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
            }
            $easemob = new Easemob();
            $token = $easemob->getToken();
            $is_send = $easemob->sendChatUser($easemob_username['c_easemob_username'],
                $easemob_username['d_easemob_username'], $data['content'], $send_data, "txt", $token);

            //消息推送
            $message = $this->Message->insertMassage($chat_data['d_id'], "doctor",
                "患者 " . $send_data['name'] . " 向您发起了图文咨询", '', '', "图文咨询", 'yes');//推送信息
            if (!$is_send || !$message) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
            }
            $message['show'] = 'yes';
            $is_send = $easemob->pushSendChat($easemob_username['d_easemob_username'],
                json_encode($message, JSON_UNESCAPED_UNICODE), $send_data, "txt", $token);

        } else {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        if ($is_send) {
            trans_commit();

            return $this->private_result("0001");
        } else {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

    }

    //评价列表
    public function commentList()
    {
        // 验token并返回id
        $token = get_token();
        $type = get_post_value('type', '');
        $grade = get_post_value('grade', '');
        $begin_time = get_post_value('begin_time', '');
        $page = get_post_value('page', 1);
        $num = get_post_value('num', 20);
        if (empty($token)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        $did = $this->UserModel->valiToken($token);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $data = $this->Consultation->getCommentList($did, $grade, $type, $begin_time, $page, $num);
        return $this->private_result(RESPONSE_SUCCESS, $data);
    }

    //评价详情
    public function commentInfo()
    {
        // 验token并返回id
        $token = get_token();
        $con_id = get_post_value('con_id', '');
        if (empty($token)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        $did = $this->UserModel->valiToken($token);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $data = $this->Consultation->getCommentInfo($did, $con_id);
        $data['impression'] = explode('|', $data['impression']);
        $data['doc_good_at'] = explode('|', $data['doc_good_at']);
        return $this->private_result(RESPONSE_SUCCESS, $data);
    }

    //问诊报告列表
    public function getReportList()
    {
        $token = get_token();
        $page = empty($_POST['page']) ? 1 : $_POST['page'];
        $num = empty($_POST['num']) ? 20 : $_POST['num'];
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $valistdate = validate_number($page) && validate_number($page);
        if (!$valistdate) {
            return $this->private_result("10002");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $ConsultationInfo = $this->Consultation->getList2("con.d_id = " . $d_id,
            "con.state = '已完成' AND ", $page, $num);
        if ($ConsultationInfo !== false) {
            return $this->private_result("0001", $ConsultationInfo);

        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function getDoctorList()
    {
        // todo: 开发完成后删除该方法
        return $this->private_result(RESPONSE_FAIL_BAD_REQUEST, "该接口已经弃用, 看见这个请通知后台管理员");
        exit();
        $area1 = get_post_value('area1', '');
        $area2 = get_post_value('area2', '');
        $department1 = get_post_value('department1', '');
        $department2 = get_post_value('department2', '');
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');

        $token = get_token();
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_regex($area1, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
            validate_regex($area2, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
            validate_regex($department1, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
            validate_regex($department2, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
            validate_number($page) &&
            validate_number($num);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $doctor = new DoctorLogic();
        $res = $doctor->searchByAreaAndDepartment($area1, $area2, $department1, $department2, $page, $num, $d_id);

        return $this->private_result(RESPONSE_SUCCESS, $res);
    }

    //获取 患者就诊轨迹
    public function getCustomerHistory()
    {
        $token = get_token();
        $c_id = get_post_value("c_id", "");
        $time = get_post_value("time", "");//根据时间范围查询  传时间戳
        $page = get_post_value("page", "");
        $num = get_post_value("num", "");
        if (empty($token) || empty($c_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($c_id);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $ConsultationInfo = $this->Consultation->getCustomerHistory($c_id, $page, $num, $time);
        if ($ConsultationInfo !== false) {
            return $this->private_result("0001", $ConsultationInfo);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //获取 患者就诊轨迹详情
    public function getCustomerHistoryInfo()
    {
        $token = get_token();
        $con_id = get_post_value("con_id", "");
        if (empty($token) || empty($con_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($con_id);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $ConsultationInfo = $this->Consultation->getHistory($con_id);
        if (empty($ConsultationInfo)) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }
        if ($ConsultationInfo !== false) {
            return $this->private_result("0001", $ConsultationInfo);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //获取 转诊聊天记录
    public function getExtendChat()
    {
        $token = get_token();
        $con_id = get_post_value("con_id", "");//当前问诊ID
        $page = get_post_value('page', 1);
        $num = get_post_value('num', 20);

        if (empty($token) || empty($con_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($con_id) && validate_number($page) && validate_number($num);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $checkExtend = $this->Consultation->checkExtend($con_id, $d_id);
        if (empty($checkExtend)) {
            return $this->private_result('40001');
        }
        $chat_list = $this->ChatModel->getExtend($con_id, $d_id, $page, $num);
        if ($chat_list !== false) {
            return $this->private_result("0001", array("chat" => $chat_list));
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function getExtendInfo()
    {
        $token = get_token();
        $con_id = get_post_value("con_id", ""); //当前问诊ID
        if (empty($token) || empty($con_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($con_id);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $checkExtend = $this->Consultation->checkExtend($con_id, $d_id);
        if (empty($checkExtend)) {
            return $this->private_result('40001');
        }
        $info = $this->ChatModel->getExtendInfo($con_id, $d_id);
        if ($info !== false) {
            return $this->private_result("0001", $info);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function getExtendList()
    {
        $token = get_token();
        $type = get_post_value("type", "");//当前问诊ID
        $page = get_post_value("page", "");
        $num = get_post_value("num", "");
        if (empty($token) || empty($type)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_regex($type, '/^(in|out)$/');
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $extendList = $this->Consultation->getExtendList($d_id, $type, $page, $num);
        if ($extendList !== false) {
            return $this->private_result("0001", $extendList);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //电话 视频 预约
    public function uploadAppointment()
    {
        // todo: 删除这段代码
        return $this->private_result(RESPONSE_FAIL_BAD_REQUEST, "该接口已更新, 请使用新接口doctor/Consultation/uploadAppointment");
        exit();
        $token = get_token();//用户ID
        $d_id = get_post_value("d_id");
        $data['name'] = get_post_value("name");
        $data['age'] = get_post_value('age');
        $data['gender'] = get_post_value('gender');
        $data['department'] = get_post_value('department');
        $data['content'] = get_post_value('content');//病历详情
        $type = get_post_value('type');//咨询类型
        $time = intval(get_post_value('time'));//预约时间  时间戳
        $during = intval(get_post_value('during'));//预约时长 min

        if (empty($token) || empty($d_id) || empty($type) || empty($data['name']) || empty($data['age']) || empty($data['gender']) || empty($data['department']) || empty($data['content'])) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($data['age'])
            && validate_number($d_id)
            && validate_regex($type, '/^(视频咨询|电话咨询)$/')
            && validate_regex($data['gender'], '/^(男|女)$/')
            && $this->validate_time($time)
            && validate_number($during);

        if (!$validateResult || strlen($data['content']) > 200 || !($during <= 30 && $during >= 10)) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $c_id = $this->CustomerLogic->valiSession($token);
        if (!$c_id) {
            return $this->private_result('10003');
        }
        if ($time < time() + 10 * 60) {
            return $this->private_result('40015');//十分钟前不可再预约
        }
        /** 验证是否可预约*/
        $timeArray = $this->get_time($time);
        $timeLine = $this->TimelineModel->find(array('d_id' => $d_id));
        $setting = customUnserialize($timeLine['schedule']);//医生设置
        $schedule = customUnserialize($timeLine[$timeArray['day'] . '_s']);//该日预约

        if (!($setting[$timeArray['hour']] === 'yes' && $schedule[$timeArray['hour']] === 'yes')) {
            return $this->private_result("40015");
        }

        try {
            $customer = Db::name('Customer')->where(array("c_id" => $c_id))->field("nick_name,money")->find();
        } catch (Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $doctor = $this->UserModel->getDoctor(array("d_id" => $d_id, "audit_status" => "yes"),
            "phone_price,video_price,easemob_username,is_open_phone,is_open_video");
        if (!$doctor) {
            return $this->private_result("40001");
        }

        if ($type == "视频咨询") {
            if ($doctor['is_open_video'] == 'no') {
                $this->private_result("40020");
            }
            $d_price = $doctor['video_price'];
        } else {
            if ($doctor['is_open_phone'] == 'no') {
                $this->private_result("40020");
            }
            $d_price = $doctor['phone_price'];
        }
        $price = $d_price * $during;//价格
        if ($customer['money'] < $price) {
            return $this->private_result('30016');
        }


        /* 获取病历参数 begin*/
        $data['c_id'] = $c_id;
        $data = $this->getProfile($data);
        /* 获取病历参数 end*/


        trans_start();
        try {
            Db::name('customer')->where(array("c_id" => $c_id))->update(array("money" => $customer['money'] - $price));//扣钱
        } catch (Exception $e) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $cp_id = $this->Consultation->addProfile($data);//添加病历信息
        if ($cp_id === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        /** 生成预约记录 并储存*/
        $appointment['cp_id'] = $cp_id;
        $appointment['d_id'] = $d_id;
        $appointment['c_id'] = $c_id;
        $appointment['type'] = $type;
        $appointment['price'] = $price;
        $appointment['create_time'] = time();
        $appointment['appoint_date'] = date("YmdHis", $time);
        $appointment['appoint_time'] = $time;
        $appointment['during'] = $during;

        $bool = $this->Consultation->insertAppointment($appointment);//添加预约记录

        if ($bool === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $easemob = new Easemob();
        $token = $easemob->getToken();

        //消息推送
        $message = $this->Message->insertMassage($d_id, "doctor",
            "患者 " . $customer['nick_name'] . " 向您发起了" . $type, '', '', $type, 'yes');//推送信息
        if (!$message) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $message['show'] = 'yes';
        $is_send = $easemob->pushSendChat($doctor['easemob_username'],
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);

        if ($is_send) {
            trans_commit();
            return $this->private_result("0001");
        } else {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    /**
     * 接受 电话/视频 预约
     */
    public function acceptAppointment()
    {
        $token = get_token();//医生
        $ap_id = get_post_value("ap_id");//预约ID

        if (empty($token) || empty($ap_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($ap_id);

        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $appoint = $this->Consultation->getAppointment(array("ap_id" => $ap_id, "d_id" => $d_id),
            "ap_id,c_id,cp_id,type,status,appoint_time,price,during,se_id");

        if (empty($appoint) || empty($appoint['cp_id'])) {
            return $this->private_result("40001");
        }
        if ($appoint['status'] == "no") {
            return $this->private_result("40016");
        } else if ($appoint['status'] == "yes") {
            return $this->private_result("40017");
        }
        if ($appoint['appoint_time'] < time()) {
            return $this->private_result("40016");
        }

        try {
            $doctor = $this->UserModel->getDoctor(array("d_id" => $d_id), "real_name");
            $customer = $this->CustomerLogic->getDetail($appoint['c_id']);
        } catch (Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }


        $timeArray = $this->get_time($appoint['appoint_time']);
        $scheduleKey = $timeArray['day'] . '_s';
        $timeLine = $this->TimelineModel->find(array("d_id" => $d_id), $scheduleKey);
        $schedule = customUnserialize($timeLine[$scheduleKey]);
        if ($schedule[$timeArray['hour']] == 'no') {
            return $this->private_result("40018");
        }

        $schedule[$timeArray['hour']] = 'no';//接受预约修改状态
        $timeLine[$scheduleKey] = customSerialize($schedule);
        trans_start();
        $is_timeLine = $this->TimelineModel->set(array("d_id" => $d_id), $timeLine);
        $is_appoint = $this->Consultation->updateAppointment(array("ap_id" => $ap_id), array("status" => "yes"));
        $add_con = $this->CustomerLogic->addOtherConsultation($customer['c_id'], $d_id, $appoint['price'],
            $appoint['type'], $appoint['during'], $appoint['ap_id'], $appoint['appoint_time'], $appoint['cp_id'], $appoint['se_id']);//新建问诊记录
        // 12.19 update cp表添加con_id --fio
        $update_cp = ConsultationLogic::relatedProfile($appoint['cp_id'], $add_con['con_id']);
        if (!($is_timeLine && $is_appoint && $add_con && $update_cp)) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }


        //消息推送
        $message = $this->Message->insertMassage($appoint['c_id'], "customer",
            $doctor['real_name'] . " 接受了您的" . $appoint['type'] . '预约申请', '', '', $appoint['type'], 'yes');//推送信息
        if (!$message) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $message['show'] = 'yes';
        $is_send = $easemob->pushSendChat($customer['easemob_username'],
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        if ($is_send) {
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

    }

    /**
     * 后台专用
     * 取消 电话/视频 预约
     * @return int
     */
    public function closeAppoint()
    {
        $token = get_post_value("token");
        $validateResult = empty($token);
        if ($validateResult) {
            exit;
            //return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        if (ADMIN_TOKEN != $token) {
            exit;
            //return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $time = time() - 60 * 5;
        $appointArray = $this->Consultation->selectAppointment(" ap.appoint_time < {$time} AND ap.status = 'wait'");
        $defaultReason = '由于医生繁忙，您的预约没有成功，请预约下一个时段';
        $messages = array();
        $easemobArray = array();
        $current = time();
        $finance = new Finance();
        foreach ($appointArray as $item) {
            $updateId = $item['ap_id'];
            trans_start();
            $bool = Db::query("update yyb_appointment as ap, yyb_customer as cu set ap.status = 'no', cu.money = ap.price + cu.money where ap.ap_id = '{$updateId}' and ap.c_id = cu.c_id ");
            // todo: 修改 order 状态
            //插入交易记录
            $extra = serialize(array("ap_id" => $updateId, 'type' => $item['type']));
            $is_finance = $finance->insert($item['cid'], "customer", $item['price'], 'refund', 'in', $extra, $time);
            if (!$is_finance) {
                trans_rollback();
            }
            trans_commit();

            if ($bool !== false) {
                $message = [];
                $message['user_id'] = $item['c_id'];
                $message['user_type'] = "customer";
                $message['content'] = $defaultReason;
                $message['extra'] = $item['reason'];
                $message['sub_type'] = $item['type'];
                $message['title'] = empty($title) ? '优医惠' : $title;
                $message['create_time'] = $current;
                $message['status'] = 'yes';

                $messages[] = $message;
                $message['easemob_username'] = $item['easemob_username'];
                $easemobArray[] = $message;
            }
        }
        $result = $this->Message->insertAll($messages); //推送信息
        if (!$result) {
            exit;
        }
        $message['show'] = 'yes';
        foreach ($easemobArray as $message) {
            $easemob = new Easemob();
            $token = $easemob->getToken();
            $easemob->pushSendChat($message['easemob_username'],
                json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        }
        return 1;
    }

    /**
     * 拒绝 电话/视频 预约
     */
    public function cancelAppoint()
    {
        $token = get_token();
        $ap_id = get_post_value("ap_id");//预约ID
        $reason = get_post_value("reason", "");
        $member_type = get_post_value("member_type", "");
        if (empty($token) || empty($ap_id) || empty($member_type)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($ap_id) && validate_regex($member_type, '/^(doctor|customer)$/');

        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        if ($member_type == 'doctor') {
            $user_id = $this->UserModel->valiToken($token);
            $where['ap.d_id'] = $user_id;
        } else {
            $user_id = $this->CustomerLogic->valiSession($token);
            $where['ap.c_id'] = $user_id;
        }
        if (!$user_id) {
            return $this->private_result('10003');
        }
        $where['ap.ap_id'] = $ap_id;
        $where['ap.status'] = 'wait';
        $appoint = $this->Consultation->findAppointment($where);

        if (empty($appoint)) {
            return $this->private_result("40001");
        } else if ($appoint['status'] == 'no') {
            return $this->private_result("40016");
        } else if ($appoint['status'] == 'yes') {
            return $this->private_result("40017");
        } else if ($appoint['appoint_time'] < time() - 60 * 10) {
            return $this->private_result("40019");
        }
        if ($member_type == 'doctor') {
            $reason = empty($reason) ? '由于医生繁忙，您的预约没有成功，请预约下一个时段' : $reason;
            $message_content = '由于医生繁忙，您的预约没有成功，请预约下一个时段';
            $appoint_user_id = $appoint['c_id'];
            $appoint_member_type = 'customer';
            $easemob_username = $appoint['c_easemob_username'];
        } else {
            $reason = empty($reason) ? "患者" . $appoint['nick_name'] . "取消了与您的" . $appoint['type'] . "预约" : $reason;
            $message_content = "患者" . $appoint['nick_name'] . "取消了与您的" . $appoint['type'] . "预约";
            $appoint_user_id = $appoint['d_id'];
            $appoint_member_type = 'doctor';
            $easemob_username = $appoint['d_easemob_username'];
        }

        $current = time();
        trans_start();
        $bool = Db::query("update yyb_appointment as ap, yyb_customer as cu set ap.status = 'no',ap.reason = '{$reason}', cu.money = ap.price + cu.money where ap.ap_id = '{$ap_id}' and ap.c_id = cu.c_id ");
        // todo: 修改order状态
        if ($bool === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        //插入交易记录
        $extra = serialize(array("ap_id" => $appoint['ap_id'], 'type' => $appoint['type']));
        $finance = new Finance();
        $is_finance = $finance->insert($appoint['c_id'], "customer", $appoint['price'], 'refund', 'in', $extra,
            $current);
        if ($is_finance === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $message['user_id'] = $appoint_user_id;
        $message['user_type'] = $appoint_member_type;
        $message['content'] = $message_content;
        $message['extra'] = $reason;
        $message['sub_type'] = $appoint['type'];
        $message['title'] = empty($title) ? '优医惠' : $title;
        $message['create_time'] = $current;
        $message['status'] = 'yes';
        $result = $this->Message->insert($message); //推送信息
        if ($result === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $message['show'] = 'yes';
        $easemob->pushSendChat($easemob_username,
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        trans_commit();
        return $this->private_result(RESPONSE_SUCCESS);
    }

    /**
     * 取消 电话/视频 预约
     */
    public function undoConsultation()
    {
        $token = get_token();
        $con_id = get_post_value("con_id");//预约ID
        $reason = get_post_value("reason", "");
        $member_type = get_post_value("member_type", "");
        if (empty($token) || empty($con_id) || empty($member_type)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($con_id) && validate_regex($member_type, '/^(doctor|customer)$/');

        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        if ($member_type == 'doctor') {
            $user_id = $this->UserModel->valiToken($token);
            $where['ap.d_id'] = $user_id;
        } else {
            $user_id = $this->CustomerLogic->valiSession($token);
            $where['ap.c_id'] = $user_id;
        }
        if (!$user_id) {
            return $this->private_result('10003');
        }
        $consultation = $this->Consultation->get(array("con_id" => $con_id), "ap_id,state,type");
        if (!$consultation) {
            return $this->private_result("40001");
        }
        if ($consultation['state'] == '已取消') {
            return $this->private_result("40016");
        }

        $ap_id = $consultation['ap_id'];
        $where['ap.ap_id'] = $ap_id;
        $where['ap.status'] = 'yes';
        $appoint = $this->Consultation->findAppointment($where);

        if (empty($appoint)) {
            return $this->private_result("40001");
        } else if ($appoint['status'] == 'no') {
            return $this->private_result("40016");
        } else if ($appoint['status'] == 'wait') {
            return $this->private_result("40023");
        } else if ($appoint['appoint_time'] < time() - 60 * 10) {
            return $this->private_result("40019");
        }
        if ($member_type == 'doctor') {
            $reason = empty($reason) ? '由于医生繁忙，您的预约已被取消，请预约下一个时段' : $reason;
            $message_content = '由于医生繁忙，您的预约已被取消，请预约下一个时段';
            $appoint_user_id = $appoint['c_id'];
            $appoint_member_type = 'customer';
            $easemob_username = $appoint['c_easemob_username'];
        } else {
            $reason = empty($reason) ? "患者" . $appoint['nick_name'] . "取消了与您的" . $appoint['type'] . "预约" : $reason;
            $message_content = "患者" . $appoint['nick_name'] . "取消了与您的" . $appoint['type'] . "预约";
            $appoint_user_id = $appoint['d_id'];
            $appoint_member_type = 'doctor';
            $easemob_username = $appoint['d_easemob_username'];
        }

        $current = time();
        trans_start();
        $set_consultation = $this->Consultation->set(array("con_id" => $con_id), array("state" => "已取消"));
        if ($set_consultation === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        // 预约状态改为no, 用户余额加回去, 订单加上退款
        $goodsType = ($consultation['type'] == '电话咨询') ? 'phone' : 'video';
        $bool = Db::query("update yyb_appointment as ap, yyb_customer as cu, yyb_order as ord set ap.status = 'no',ap.reason = '{$reason}', ord.is_refund = 'yes', ord.refund_time = {$current}, cu.money = ap.price + cu.money where ap.ap_id = '{$ap_id}' AND ap.c_id = cu.c_id  AND ord.goods_type = '{$goodsType}' AND ord.is_refund = 'no' AND ord.goods_id = '{$ap_id}'");
        if ($bool === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        //插入交易记录
        $extra = serialize(array("ap_id" => $appoint['ap_id'], 'type' => $appoint['type']));
        $finance = new Finance();
        $is_finance = $finance->insert($appoint['c_id'], "customer", $appoint['price'], 'refund', 'in', $extra,
            $current);
        if ($is_finance === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $message['user_id'] = $appoint_user_id;
        $message['user_type'] = $appoint_member_type;
        $message['content'] = $message_content;
        $message['extra'] = $reason;
        $message['sub_type'] = $appoint['type'];
        $message['title'] = empty($title) ? '优医惠' : $title;
        $message['create_time'] = $current;
        $message['status'] = 'yes';
        $result = $this->Message->insert($message); //推送信息
        if ($result === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $message['show'] = 'yes';
        $easemob->pushSendChat($easemob_username,
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        trans_commit();
        return $this->private_result(RESPONSE_SUCCESS);
    }

    /**
     * 接受 私人医生/院后指导 预约
     */
    public function acceptService()
    {
        $token = get_token();//医生
        $se_id = get_post_value("se_id");//预约ID

        if (empty($token) || empty($se_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($se_id);

        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $service = $this->Consultation->findService(array("se.se_id" => $se_id, "se.d_id" => $d_id));

        if (empty($service)) {
            return $this->private_result("40001");
        }
        if ($service['status'] == "no") {
            return $this->private_result("40016");
        } else if ($service['status'] == "yes") {
            return $this->private_result("40017");
        }


        $private_time = array('7' => "一周", '30' => "1个月", '90' => "3个月", '180' => "1个月", '360' => "12个月");
        if ($service['type'] == '私人医生') {
            $service_time = $private_time[$service['during']];
        } else {
            $service_time = $service['during'] . "天";
        }
        $message_content = $service['real_name'] . " 医生接受了您为期" . $service_time . '的' . $service['type'] . '服务，请点击查看详细原因';

        trans_start();
        $is_service = $this->Consultation->updateService(array("se_id" => $se_id),
            array("status" => "yes", "reason" => $message_content));
        $add_con = $this->CustomerLogic->addServiceConsultation($service['c_id'], $d_id, $service['money'],
            "图文咨询", $service['during'], $se_id, $service['end_time'], $service['cp_id']);//新建问诊记录
        // 12.19 update cp表添加con_id --fio
        $update_cp = ConsultationLogic::relatedProfile($service['cp_id'], $add_con['con_id']);
        if (!($is_service && $add_con && $update_cp)) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        //消息推送
        $message = $this->Message->insertMassage($service['c_id'], "customer",
            $message_content, '', '', $service['type'], 'yes');//推送信息
        if (!$message) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $message['show'] = 'yes';
        $is_send = $easemob->pushSendChat($service['c_easemob_username'],
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        if ($is_send) {
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

    }

    /**
     * 医生端使用
     * 取消 私人医生 院后指导预约
     * @return int
     */
    public function cancelService()
    {
        $token = get_token();
        $se_id = get_post_value("se_id");//预约ID
        $reason = get_post_value("reason", "");
        if (empty($token) || empty($se_id)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($se_id);

        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $where['se.se_id'] = $se_id;
        $where['se.d_id'] = $d_id;
        $service = $this->Consultation->findService($where);
        if (empty($service)) {
            return $this->private_result("40001");
        } else if ($service['status'] == 'no') {
            return $this->private_result("40016");
        } else if ($service['status'] == 'yes' || $service['status'] == 'end') {
            return $this->private_result("40017");
        }

        $private_time = array('7' => "一周", '30' => "1个月", '90' => "3个月", '180' => "1个月", '360' => "12个月");
        if ($service['type'] == '私人医生') {
            $service_time = $private_time[$service['during']];
            $goods_type = 'private';
        } else {
            $service_time = $service['during'] . "天";
            $goods_type = 'guidance';
        }

        $reason = empty($reason) ? '由于医生繁忙，您的预约没有成功，请稍后再预约' : $reason;
        $message_content = $service['real_name'] . '医生拒绝了您为期' . $service_time . '的' . $service['type'] . '服务，请点击查看详细原因';
        $service_user_id = $service['c_id'];
        $service_member_type = 'customer';
        $easemob_username = $service['c_easemob_username'];

        $current = time();
        trans_start();
        $bool = Db::query("update yyb_service as se, yyb_customer as cu,yyb_order as ord set se.status = 'no', cu.money = se.money + cu.money,ord.is_refund = 'yes',ord.refund_time = '{$current}',se.reason = '{$reason}' where se.se_id = '{$se_id}' and se.c_id = cu.c_id and ord.goods_id = se.se_id and ord.is_refund = 'no'");
        if ($bool === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        //插入交易记录
        $extra = serialize(array("se_id" => $service['se_id'], 'type' => $service['type']));
        $finance = new Finance();
        $is_finance = $finance->insert($service['c_id'], "customer", $service['money'], 'refund', 'in', $extra,
            $current);
        if ($is_finance === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }

        $message['user_id'] = $service_user_id;
        $message['user_type'] = $service_member_type;
        $message['content'] = $message_content;
        $message['extra'] = $reason;
        $message['sub_type'] = $service['type'];
        $message['title'] = empty($title) ? '优医惠' : $title;
        $message['create_time'] = $current;
        $message['status'] = 'yes';
        $result = $this->Message->insert($message); //推送信息
        if ($result === false) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $message['show'] = 'yes';
        $easemob->pushSendChat($easemob_username,
            json_encode($message, JSON_UNESCAPED_UNICODE), "", "txt", $token);
        trans_commit();
        return $this->private_result(RESPONSE_SUCCESS);
    }

    //验证预约时间戳
    private function validate_time($time)
    {
        $now_day = date("d", time());
        $vali_day = date("d", $time);
        $vali_hour = date("H", $time);
        $vali_minute = date("i", $time);
        $vali_second = date("s", $time);
        $bool = true;
        if ($vali_day != $now_day && $vali_day != $now_day + 1) {
            $bool = false;
        }
        if ($vali_hour > 22 || $vali_hour < 8) {
            $bool = false;
        }
        if ($vali_minute != 00 && $vali_minute != 30) {
            $bool = false;
        }
        if ($vali_second != 00) {
            $bool = false;
        }
        return $bool;
    }

    //获取预约 时间点
    private function get_time($time)
    {
        $now_day = date("d", time());
        $day = date("d", $time);
        $hour = date("Hi", $time);
        if ($day == $now_day) {
            $date['day'] = "today";
        } else {
            $date['day'] = "tomorrow";
        }
        $date['hour'] = intval($hour);
        return $date;
    }

    private function getProfile($data)
    {
        if (get_post_value('blood_type')) {
            $data['blood_type'] = get_post_value('blood_type');
        }
        if (get_post_value('drink')) {
            $data['drink'] = get_post_value('drink');
        }
        if (get_post_value('is_born')) {
            $data['is_born'] = get_post_value('is_born');
            $data['born_time'] = get_post_value('born_time');
            $data['born_type'] = get_post_value('born_type');
        }
        if (get_post_value('is_allergy')) {
            $data['is_allergy'] = get_post_value('is_allergy');
            $data['allergy'] = get_post_value('allergy');
        }
        if (get_post_value('smoke')) {
            $data['smoke'] = get_post_value('smoke');
        }
        if (get_post_value('genetic_disease')) {
            $data['genetic_disease'] = get_post_value('genetic_disease');
            $data['has_genetic_disease'] = get_post_value('has_genetic_disease');
        }
        if (get_post_value('operation_history')) {
            $data['operation_history'] = get_post_value('operation_history');
        }
        if (get_post_value('semen_volume')) {
            $data['semen_volume'] = get_post_value('semen_volume');
        }
        if (get_post_value('semen_density')) {
            $data['semen_density'] = get_post_value('semen_density');
        }
        if (get_post_value('masturbation_history')) {
            $data['masturbation_history'] = get_post_value('masturbation_history');
        }
        if (get_post_value('abstinent_days')) {
            $data['abstinent_days'] = get_post_value('abstinent_days');
        }
        if (get_post_value('prepare_pregnant_time')) {
            $data['prepare_pregnant_time'] = get_post_value('prepare_pregnant_time');
        }
        return $data;
    }
}
