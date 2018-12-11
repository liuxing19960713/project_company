<?php
//用户登录model
namespace app\doctor\model;

use think\Db;
use think\Model;
use think\Exception;

class Consultation
{

    public function getList($d_id, $where, $page = 1, $num = 20)
    {
        try {
            $where .= "con.d_id = " . $d_id;
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,con.type as type,con.create_time as create_time,con.state as state,con.appoint_time as appoint_time,con.is_extend as is_extend,con.total_time as total_time,con.money as money,con.call_data";
            $field .= ",cp.name as name,cp.age as age,cp.gender as gender,cp.content as content";
            $field .= ",cp.department as department,cp.genetic_disease as genetic_disease,cp.operation_history as operation_history,cp.has_genetic_disease as has_genetic_disease,
            cp.semen_volume as semen_volume,cp.semen_density as semen_density,cp.masturbation_history as masturbation_history,cp.abstinent_days as abstinent_days,cp.prepare_pregnant_time as prepare_pregnant_time,
            cp.is_allergy as is_allergy,cp.blood_type as blood_type,cp.drink as drink,cp.is_born as born,cp.born_time as born_time,cp.born_type as born_type,cp.allergy as allrtgy,cp.smoke as smoke";
            $field .= ",c.avatar as avatar,c.easemob_username as c_easemob_username,d.easemob_username as d_easemob_username";
            $data = Db::table('yyb_consultation')->alias('con')
                ->field($field)
                ->join('yyb_consultation_profile cp', 'con.con_id = cp.con_id')
                ->join('yyb_customer c', 'c.c_id =con.c_id')
                ->join('yyb_doctor d', 'd.d_id =con.d_id')
                ->where($where)
                ->order("con.create_time DESC")
                ->page($page, $num)->select();
            if (!empty($data)) {
                // 很恶心的, 只是为了查电话有没有挂断
                foreach ($data as &$item) {
                    if ($item['type'] == '电话咨询' || $item['type'] == '视频咨询') {
                        $item['call_data'] = $this->getCallDataStatus($item['call_data']);
                    } else {
                        $item['call_data'] = 'no';
                    }
                }
            }
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    //查询已完成的记录  联多一张report表
    public function getList2($member, $where, $page = 1, $num = 20)
    {
        try {
            $where .= $member;
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,con.type as type,con.create_time as create_time,re.report_time as end_time,con.state as state,c.avatar as avatar,cp.name as name,cp.age as age,cp.gender as gender,cp.content as content";
            $field .= ",cp.department as department,cp.genetic_disease as genetic_disease,cp.operation_history as operation_history,cp.has_genetic_disease as has_genetic_disease,
            cp.semen_volume as semen_volume,cp.semen_density as semen_density,cp.masturbation_history as masturbation_history,cp.abstinent_days as abstinent_days,cp.prepare_pregnant_time as prepare_pregnant_time,
            cp.is_allergy as is_allergy,cp.blood_type as blood_type,cp.drink as drink,cp.is_born as born,cp.born_time as born_time,cp.born_type as born_type,cp.allergy as allrtgy,cp.smoke as smoke,con.appoint_time as appoint_time,re.diagnose,re.advise";
            $field .= ",c.easemob_username as c_easemob_username,d.easemob_username as d_easemob_username";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)->join('yyb_consultation_profile cp',
                'con.con_id = cp.con_id')->join('yyb_customer c', 'c.c_id =con.c_id')->join('yyb_doctor d',
                'd.d_id =con.d_id')->join('yyb_consultation_report re',
                're.con_id =con.con_id')->field($field)->order("con.create_time DESC")->page($page,
                $num)->select();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function select($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('consultation')->where($where)->select();
            } else {
                $company_info = Db::name('consultation')->where($where)->field($field)->select();
            }
            return $company_info;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function get($where, $field = '')
    {
        try {
            if ($field) {
                $consultation = Db::name('consultation')->where($where)->field($field)->find();
            } else {
                $consultation = Db::name('consultation')->where($where)->find();
            }
            return $consultation;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getEx($where, $field = '')
    {
        try {
            if ($field) {
                $consultation = Db::name('ex_consultation')->where($where)->field($field)->find();
            } else {
                $consultation = Db::name('ex_consultation')->where($where)->find();
            }
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getReport($where, $field = '')
    {
        try {
            if ($field) {
                $consultation = Db::name('consultation_report')->where($where)->field($field)->find();
            } else {
                $consultation = Db::name('consultation_report')->where($where)->find();
            }
            return $consultation;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getProfile($where, $field = '')
    {
        try {
            if ($field) {
                $consultation = Db::name('consultation_profile')->where($where)->field($field)->find();
            } else {
                $consultation = Db::name('consultation_profile')->where($where)->find();
            }
            return $consultation;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getConsultationInfo($con_id, $where = "")
    {
        try {
            $where = "con.con_id = " . $con_id . $where;
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,c.easemob_username as c_easemob_username,d.easemob_username as d_easemob_username,con.create_time as create_time,con.create_time+con.total_time as end_time,con.state as state,c.avatar as avatar,cp.name as name,cp.age as age,cp.gender as gender,cp.content as content";
            $field .= ",cp.blood_type as blood_type,cp.drink as drink,cp.is_born as born,cp.born_time as born_time,cp.born_type as born_type,cp.allergy as allrtgy,cp.smoke as smoke";
            $field .= ",cp.genetic_disease as genetic_disease,cp.has_genetic_disease as has_genetic_disease,cp.operation_history as operation_history,cp.semen_volume as semen_volume,cp.semen_density as semen_density,cp.masturbation_history as masturbation_history,cp.department as department,cp.abstinent_days as abstinent_days,cp.prepare_pregnant_time as prepare_pregnant_time,cp.is_allergy as is_allergy";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)->join('yyb_consultation_profile cp',
                'con.con_id = cp.con_id')->join('yyb_customer c', 'c.c_id =con.c_id')->join('yyb_doctor d',
                'd.d_id =con.d_id')->field($field)->find();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getConsultationInfoByCp($cp_id, $where = "")
    {
        try {
            $where = "cp.cp_id = " . $cp_id . $where;

            $field = "cp.c_id as c_id";
            $field .= ", c.easemob_username as c_easemob_username, c.avatar as avatar";
            $field .= ", cp.name as name, cp.age as age,cp.gender as gender,cp.content as content";
            $field .= ",cp.blood_type as blood_type,cp.drink as drink,cp.is_born as born,cp.born_time as born_time,cp.born_type as born_type,cp.allergy as allrtgy,cp.smoke as smoke";
            $field .= ",cp.genetic_disease as genetic_disease,cp.has_genetic_disease as has_genetic_disease,cp.operation_history as operation_history,cp.semen_volume as semen_volume,cp.semen_density as semen_density,cp.masturbation_history as masturbation_history,cp.department as department,cp.abstinent_days as abstinent_days,cp.prepare_pregnant_time as prepare_pregnant_time,cp.is_allergy as is_allergy";
            $ConsultationInfo = Db::table('yyb_consultation_profile')->alias('cp')
                ->join('yyb_customer c', 'c.c_id = cp.c_id')
                ->where($where)
                ->field($field)->find();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function set($where, $save)
    {
        try {
            $set = Db::name('consultation')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function add($data)
    {
        try {
            $add = Db::name('consultation')->insert($data);
            return $add;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function addReport($add)
    {
        try {
            $add = Db::name('consultation_report')->insertGetId($add);
            return $add;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function addProfile($add)
    {
        try {
            $add = Db::name('consultation_profile')->insertGetId($add);
            return $add;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('consultation')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getCommentList($d_id, $grade = "", $type = "", $begin_time = '', $page = 1, $num = 20)
    {
        try {
            $where = "con.state = '已完成' AND con.comment_time != 0 AND con.d_id = " . $d_id;
            if (!empty($grade)) {
                $where .= " AND con.grade = '" . $grade . "'";
            }
            if (!empty($type)) {
                $where .= " AND con.type = '" . $type . "'";
            }
            if (!empty($begin_time)) {
                $where .= " AND con.comment_time >= '$begin_time'";
            }
            $field = "con.con_id as con_id,con.c_id as c_id,c.avatar as avatar,c.nick_name as nick_name,c.age as age,c.gender as gender,con.comment_time as comment_time,con.type as type,con.grade as grade,con.evaluation as evaluation";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)->join('yyb_customer c',
                'c.c_id =con.c_id')->join('yyb_doctor d',
                'd.d_id =con.d_id')->field($field)->order("con.comment_time DESC")->page($page, $num)->select();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getCommentInfo($d_id, $con_id)
    {
        try {
            $where = "con.state = '已完成' AND con.comment_time != 0 AND con.d_id = " . $d_id . " AND con.con_id = " . $con_id;
            $field = "con.con_id as con_id,con.c_id as c_id,c.avatar as avatar,c.nick_name as nick_name,con.comment_time as comment_time,con.type as type,con.grade as grade,con.evaluation as evaluation,con.impression as impression,con.doc_good_at as doc_good_at";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)->join('yyb_customer c',
                'c.c_id =con.c_id')->join('yyb_doctor d', 'd.d_id =con.d_id')->field($field)->find();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getExtraInfo($con_id)
    {
        try {
            $where = "con.con_id = " . $con_id;
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,con.money as money,c.avatar as customer_avatar,cp.name as customer_name,cp.age as customer_age,cp.gender as customer_gender,cp.content as content,cp.department as department,d.real_name as doctor_name,d.avatar as doctor_avatar,d.gender as doctor_gender";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)
                ->join('yyb_customer c', 'c.c_id =con.c_id')
                ->join('yyb_doctor d', 'd.d_id =con.d_id')
                ->join('yyb_consultation_profile cp', 'con.con_id = cp.con_id', 'LEFT')
                ->field($field)->find();
            if (empty($ConsultationInfo)) {
                return [];
            }
            if (empty($ConsultationInfo['money'])) {
                $ConsultationInfo['buy_type'] = "coupon";//优惠券
            } else {
                $ConsultationInfo['buy_type'] = "cash";//付费
            }
            unset($ConsultationInfo['money']);
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getCustomerHistory($c_id, $page = 1, $num = 20, $time = 0)
    {
        try {
            $where = "con.c_id = " . $c_id;
            if (!empty($time)) {
                $where .= " AND con.create_time >= $time";
            }
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,cp.content as content,cp.department as department,con.type as type,con.create_time as create_time";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->where($where)->join('yyb_consultation_profile cp',
                'con.con_id = cp.con_id')->join('yyb_customer c', 'c.c_id =con.c_id')->join('yyb_doctor d',
                'con.d_id = d.d_id')->field($field)->order("con.create_time DESC")->page($page, $num)->select();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getHistory($con_id)
    {
        try {
            $where = "con.con_id = " . $con_id . " AND de.is_default = 'yes'";
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,d.avatar as doctor_avatar,d.real_name as doctor_name,d.good_at as good_at,de.hospital as hospital,d.title as title,con.type as type,cp.content as content,cp.department as department";
            $ConsultationInfo = Db::table('yyb_consultation')->alias('con')->join('yyb_consultation_profile cp',
                'con.con_id = cp.con_id')->join('yyb_customer c', 'c.c_id =con.c_id')->join('yyb_doctor d',
                'con.d_id = d.d_id')
                ->join('yyb_department de', 'de.d_id = d.d_id')
                ->where($where)->field($field)->order("con.create_time DESC")->find();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function checkExtend($con_id, $d_id)
    {
        try {
            $where = "con_id = " . $con_id . " AND d_id = $d_id";
            $ConsultationInfo = Db::table('yyb_ex_consultation')
                ->where($where)->find();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    //in 被转 out 转诊
    public function getExtendList($d_id, $type, $page = 1, $num = 20)
    {
        try {
            if ($type == 'in') {
                $where = 'ex.d_id = ' . $d_id;
                $temp1 = 'con.con_id = ex.con_id';
            } else {
                $where = 'ex.b_d_id = ' . $d_id;
                $temp1 = 'con.con_id = ex.b_con_id';
            }
            $field = "cp.name as customer_name,c.avatar as customer_avatar,cp.gender as customer_gender,d.real_name as doctor_name,d.avatar as doctor_avatar,d.gender as doctor_gender,ex.create_time as create_time,cp.content as content,ex.commission as commission";
            $ConsultationInfo = Db::table('yyb_ex_consultation')->where($where)->alias('ex')
                ->join('yyb_consultation con', $temp1)
                ->join('yyb_consultation_profile cp', 'con.con_id = cp.con_id')
                ->join('yyb_customer c', 'c.c_id =con.c_id')
                ->join('yyb_doctor d', 'con.d_id = d.d_id')
                ->where($where)->field($field)->order("ex.create_time DESC")->page($page, $num)->select();
            return $ConsultationInfo;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function insertAppointment($data)
    {
        try {
            $bool = Db::name('appointment')->insert($data);
            return $bool;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getAppointment($where, $field)
    {
        try {
            if ($field) {
                $data = Db::name('appointment')->where($where)->field($field)->find();
            } else {
                $data = Db::name('appointment')->where($where)->find();
            }
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function selectAppointment($where)
    {
        try {
            $field = 'ap.ap_id, ap.c_id, ap.type, ap.reason, cu.nick_name, cu.easemob_username,ap.price';
            $data = Db::name('appointment')->alias('ap')->join("yyb_customer cu", "ap.c_id = cu.c_id")->field($field)
                ->where($where)
                ->select();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function findAppointment($where)
    {
        try {
            $field = 'ap.ap_id, ap.c_id, ap.d_id,ap.type, ap.price,ap.reason, ap.appoint_time,ap.status, cu.nick_name, cu.easemob_username as c_easemob_username,cu.nick_name,do.easemob_username as d_easemob_username';
            $data = Db::name('appointment')->alias('ap')->join("yyb_customer cu",
                "ap.c_id = cu.c_id")->join("yyb_doctor do", "ap.d_id = do.d_id")->field($field)
                ->where($where)
                ->find();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function updateAppointment($where, $save)
    {
        try {
            $bool = Db::name('appointment')->where($where)->update($save);
            return $bool;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function insertOrder($data)
    {
        try {
            $or_id = Db::name('order')->insertGetId($data);
            return $or_id;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function selectOrder($where)
    {
        try {
            $field = 'or.or_id, or.c_id, or.type, or.reason, cu.nick_name, cu.easemob_username';
            $data = Db::name('order')->alias('or')->join("yyb_customer cu", "or.c_id = cu.c_id")->field($field)
                ->where($where)
                ->select();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function findOrder($where)
    {
        try {
            $field = 'ap.ap_id, ap.c_id, ap.d_id,ap.type, ap.reason, ap.appoint_time,ap.status, cu.nick_name, cu.easemob_username as c_easemob_username,cu.nick_name,do.easemob_username as d_easemob_username';
            $data = Db::name('order')->alias('ap')->join("yyb_customer cu", "ap.c_id = cu.c_id")->join("yyb_doctor do",
                "ap.d_id = do.d_id")->field($field)
                ->where($where)
                ->find();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function updateOrder($where, $save)
    {
        try {
            $bool = Db::name('order')->where($where)->update($save);
            return $bool;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function insertService($data)
    {
        try {
            $se_id = Db::name('service')->insertGetId($data);
            return $se_id;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function selectService($where)
    {
        try {
            $field = 'se.se_id, se.c_id, se.reason, se.type, se.money, se.during, doc.real_name, cu.nick_name, cu.easemob_username';
            $data = Db::name('service')->alias('se')
                ->join("yyb_customer cu", "se.c_id = cu.c_id")
                ->join("yyb_doctor doc", "doc.d_id = se.d_id")
                ->field($field)
                ->where($where)
                ->select();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function findService($where)
    {
        try {
            $field = 'se.se_id, se.c_id, se.d_id,se.type, se.create_time,se.money, se.during, se.end_time, se.status, se.cp_id';
            $field .= ', do.real_name, cu.nick_name, cu.easemob_username as c_easemob_username,cu.nick_name,do.easemob_username as d_easemob_username';
            $data = Db::name('service')->alias('se')->join("yyb_customer cu",
                "se.c_id = cu.c_id")->join("yyb_doctor do", "se.d_id = do.d_id")->field($field)
                ->where($where)
                ->find();
            return $data;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function updateService($where, $save)
    {
        try {
            $bool = Db::name('service')->where($where)->update($save);
            return $bool;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function checkService($cid, $did)
    {
        $where = "c_id = {$cid} AND d_id = {$did} AND (status = 'wait' OR status = 'yes')";
        try {
            $service = Db::name('service')->field('c_id,d_id,type,create_time,status,money,end_time,cp_id')->where($where)->order("create_time DESC")->find();
            if($service){
                if($service['status'] = 'wait'){
                    return "40024";//预约待确认
                }else{
                    if($service['end_time'] > time()){
                        return "40025";//服务正在进行
                    }else{
                        return true;
                    }
                }
            }else{
                return true;
            }
        } catch (Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function checkServiceDoctor($cid, $did)
    {
        $where = "c_id = {$cid} AND d_id = {$did} AND (status = 'wait' OR status = 'yes')";
        $service_array = array('c_id'=>0,'d_id'=>0,'cp_id'=>0,'type'=>"",'create_time'=>0,'status'=>"",'money'=>0,'end_time'=>0);//空数组
        try {
            $service = Db::name('service')->field('c_id,d_id,type,cp_id,create_time,status,money,end_time')->where($where)->order("create_time DESC")->find();
            if($service){
                if($service['status'] == 'wait'){
                    return $service;//预约待确认
                }else{
                    if($service['end_time'] > time()){
                        return $service;//服务正在进行
                    }else{
                        return $service_array;
                    }
                }
            }else{
                return $service_array;
            }
        } catch (Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }
    public function getReportList($c_id, $where = '', $page = 1, $num = 20)
    {
        try {
            $where .= "con.c_id = " . $c_id;
            $field = "con.con_id as con_id,con.c_id as c_id,con.d_id as d_id,con.type as type,con.create_time as create_time,re.report_time as end_time,con.state as state,cp.content,cp.name,cp.age,cp.gender,re.diagnose,re.advise";
            $reportList = Db::table('yyb_consultation')->alias('con')->field($field)->join('yyb_consultation_profile cp',
                'con.con_id = cp.con_id')->join('yyb_customer c', 'c.c_id =con.c_id')->join('yyb_doctor d',
                'd.d_id =con.d_id')->join('yyb_consultation_report re',
                're.con_id =con.con_id')->where($where)->order("con.create_time DESC")->page($page,
                $num)->select();
            return $reportList;
        } catch (Exception $e) {
            ex_log($e);
            return false;
        }
    }

    private function getCallDataStatus($callDataStr)
    {
        try {
            $callData = unserialize($callDataStr);

            if (!empty($callData)) {
                $lastCallData = end($callData);
                if (empty($lastCallData) || empty($lastCallData['status'])) {
                    return 'wait';
                } else {
                    return $lastCallData['status'];
                }
            } else {
                return 'wait';
            }
        } catch (\Exception $e) {
            ex_log($e);
            return 'wait';
        }

    }

}
