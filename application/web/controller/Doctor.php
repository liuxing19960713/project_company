<?php

/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/14/16
 * Time: 14:50
 */

namespace app\web\controller;

use app\index\controller\Base;
use app\web\logic\CustomerLogic;
use app\web\logic\DoctorLogic;
use app\doctor\model\User as UserModel;

class Doctor extends Base
{
    /**
     * 医生搜索
     * 从一期移过来的
     * @return \think\response\Json
     */
    public function search()
    {
        $keyword = $this->getParam('keyword', '');
//        $this->checkSingle($keyword, '', 'Base.keyword'); // '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u'

        $doctor = new DoctorLogic();
        $res = $doctor->searchByKeyword($keyword, $this->pageIndex, $this->pageSize);

        $this->addRenderData('doctors', $res, false);
        return $this->getRenderJson();
    }

    /**
     * 获取医生详情
     * 从一期移过来的
     * @return \think\response\Json
     */
    public function getDetail()
    {
        $doctorID = $this->getParam('doctor_id', '');
        $this->checkSingle($doctorID, 'id', 'Base.id');

        $doctor = new DoctorLogic();
        $res = $doctor->getDetail($doctorID);

        if (!$res) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到');
            return $this->getRenderJson();
        }

        $this->addRenderData('doctor', $res);
        return $this->getRenderJson();
    }

//    public function getComments()
//    {
//        $doctorID = get_post_value('did', '');
//        $page = get_post_value('page', '1');
//        $num = get_post_value('num', '20');
//
//        $validateResult = validate_number($doctorID) && validate_number($page) && validate_number($num) && $page > 0;
//        if (!$validateResult) {
//            if (empty($doctorID)) {
//                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
//            } else {
//                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
//            }
//        }
//
//        $doctor = new DoctorLogic();
//        $res = $doctor->getComments($doctorID, $page, $num);
//
//        if ($res === false) {
//            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
//        }
//
//        return $this->private_result(RESPONSE_SUCCESS, $res);
//    }

//    public function getList()
//    {
//        $area1 = get_post_value('area1', '');
//        $area2 = get_post_value('area2', '');
//        $department1 = get_post_value('department1', '');
//        $department2 = get_post_value('department2', '');
//        $page = get_post_value('page', '1');
//        $num = get_post_value('num', '20');
//        $type = get_post_value('type', 'image');
//
//        $validateResult = validate_regex($area1, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
//            validate_regex($area2, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
//            validate_regex($department1, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
//            validate_regex($department2, '/^[\x{4e00}-\x{9fa5}]*$/u') &&
//            validate_words($type, ['all', 'image', 'phone', 'video', 'guidance', 'private']) &&
//            validate_number($page) &&
//            validate_number($num);
//        if (!$validateResult) {
//            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
//        }
//        $doctor = new DoctorLogic();
//        $res = $doctor->searchByAreaAndDepartment($area1, $area2, $department1, $department2, $page, $num, $type);
//        return $this->private_result(RESPONSE_SUCCESS, $res);
//    }


//    public function getEXList()
//    {
//        $department = get_post_value('department', '');
//        $token = get_token();
//        if (empty($token) || empty($department)) {
//            return $this->private_result("10001");
//        }
//        $userModel = new UserModel();
//        $d_id = $userModel->valiToken($token);
//        if (!$d_id) {
//            return $this->private_result('10003');
//        }
//        if ($department === '') {
//            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
//        }
//        $validateResult = validate_regex($department, '/^[\x{4e00}-\x{9fa5}]*$/u');
//        if (!$validateResult) {
//            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
//        }
//
//        $doctor = new DoctorLogic();
//        $res = $doctor->searchByDepartment($department,$d_id);
//
//        if ($res !== false) {
//            return $this->private_result(RESPONSE_SUCCESS, $res);
//        } else {
//            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
//        }
//
//    }

//    public function getGiftList()
//    {
//        $did = get_post_value('did');
//        $page = get_post_value('page', '1');
//        $num = get_post_value('num', '20');
//
//        $validateResult = validate_number($did) && validate_number($page) && validate_number($num);
//        if (!$validateResult) {
//            if (empty($did)) {
//                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
//            }
//            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
//        }
//
//        $doctor = new DoctorLogic();
//        $res = $doctor->getGiftList($did,$page,$num);
//
//        if ($res === false) {
//            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
//        }
//        return $this->private_result(RESPONSE_SUCCESS, $res);
//    }


}
