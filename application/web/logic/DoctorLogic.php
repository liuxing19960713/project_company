<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/14/16
 * Time: 15:18
 */

namespace app\web\logic;

use app\common\model\CommPosts;
use app\common\model\Post;
use app\web\model\Consultation;
use app\web\model\DoctorApisession;
use app\web\model\Gift;
use think\Db;
use app\web\model\Doctor;

class DoctorLogic
{
    private $hiddenKeys = [];
    private $departmentHiddenKeys = [];
    private $shouldShowKeys = [];
    private $departmentShouldShowKeys = [];

    public function __construct()
    {
        $this->shouldShowKeys = [
            'doctor_id',
            'nick_name',
            'avatar',
            'title',
            'gender',
            'age',
            'birthday',
            'province',
            'city',
            'is_open_image',
            'is_open_phone',
            'is_open_video',
            'is_open_guidance',
            'is_open_private',
            'image_price',
            'phone_price',
            'video_price',
            'guidance_price',
            'private_price',
            'ex_price',
            'good_at',
            'intro1',
            'intro2',
            'intro3',
            'statistic_good_at',
            'statistic_impression',
            'follower_count',
            'service_times',
            'gift_times',
            'easemob_username'

        ];
        $this->departmentShouldShowKeys = [
            'hospital',
            'department1',
            'department2',
            'is_audited'
        ];


        $this->hiddenKeys = [
            'mobile_num',
            'qualification_back',
            'qualification_front',
            'money',
            'create_time',
            'last_login_time',
            'price_percentage',
            'ex_percentage',
            'is_push',
            'is_send',
            'audit_status',
            'audit_reason',
            'reg_code',
            'password',
            'invite_code',
            'pass_code',
            'invite_d_id',
            'id_card',
            'email',
            'easemob_password',
        ];

        $this->departmentHiddenKeys = [
            'de_id',
            'is_default',
            'department_phone',
            'audit_department_phone',
            'audit_hospital',
            'audit_department1',
            'audit_department2',
            'feedback'
        ];
    }

    private function getDoctorKeys($alias = null)
    {
        if (empty($alias)) {
            return $this->shouldShowKeys;
        } else {
            $res = [];
            foreach ($this->shouldShowKeys as $item) {
                $res[] = $alias . "." . $item;
            }
            return $res;
        }
    }

    private function getDepartmentKeys($alias = null)
    {
        if (empty($alias)) {
            return $this->departmentShouldShowKeys;
        } else {
            $res = [];
            foreach ($this->departmentShouldShowKeys as $item) {
                $res[] = $alias . "." . $item;
            }
            return $res;
        }
    }

    /**
     * 搜索医生
     * update: 只保留按姓名搜索
     * @param     $keyword
     * @param int $page
     * @param int $num
     *
     * @return array
     */
    public function searchByKeyword($keyword, $page = 1, $num = 20)
    {
        $whereQuery = [
            'is_deleted' => 'no',
            'department_parent' => ['neq', ''],
        ];
        if ($keyword != '' && !is_null($keyword)) {
            $searchableKeys =
                "nick_name|good_at|intro1|intro2|intro3|hospital|department_parent|department_child";
            $keyword = "REGEXP '{$keyword}'";
            $whereQuery[$searchableKeys] = ['exp', $keyword];
        }
        $page = "{$page}, {$num}";
        $order = 'service_times DESC';
        $fieldList = [
            'doctor_id', 'nick_name', 'avatar', 'title', 'gender', 'good_at',
            'hospital', 'department_parent', 'department_child',
            'is_open_image', 'image_price'
        ];

        $doctor = new Doctor();
        $doctorModelList = $doctor->audit()
            ->field($fieldList)
            ->where($whereQuery)->order($order)->page($page)->select();

        $doctorIdList = [];
        foreach ($doctorModelList as $item) {
            $doctorIdList[] = $item['doctor_id'];
        }
        // 查询咨询次数
        $serviceTimesField = [
            "type",
            "count(`type`) as c"
        ];
        foreach ($doctorModelList as $item) {
            $serviceTimes = Consultation::build()
                ->field($serviceTimesField)
                ->where("d_id", $item['doctor_id'])
                ->where("state", "已完成")
                ->where("type", "图文咨询")
                ->group('type')
                ->find();
            $item['image_service_times'] = $serviceTimes['c'] ?: 0;
        }

        return $doctorModelList;
    }

    public function getList($requestParams, $page = 1, $num = 20)
    {
        $whereQuery = [
            'is_deleted' => 'no',
            'department_parent' => ['neq', ''],
        ];
        if (!empty($requestParams['province'])) {
            $whereQuery['province'] = $requestParams['province'];
        }
        if (!empty($requestParams['city'])) {
            $whereQuery['city'] = $requestParams['city'];
        }
        if (!empty($requestParams['con_type'])) {
            if (in_array($requestParams['con_type'], ['image', 'phone', 'video', 'private'])) {
                $whereQuery['is_open_' . $requestParams['con_type']] = 'yes';
                if ($requestParams['con_type'] != 'private') {
                    $whereQuery[$requestParams['con_type'] . '_price'] = [ 'neq', 0];
                }
            }
        }
        $page = "{$page}, {$num}";
        $order = 'service_times DESC';
        if (!empty($requestParams['sort_type']) && $requestParams['sort_type'] == 'asc') {
            $order = 'service_times ASC';
        }
        $fieldList = [
            'doctor_id', 'nick_name', 'avatar', 'title', 'gender', 'good_at',
            'hospital', 'department_parent', 'department_child',
            'is_open_image', 'image_price'
        ];

        $doctor = new Doctor();
        $doctorModelList = $doctor->audit()
            ->field($fieldList)
            ->where($whereQuery)->order($order)->page($page)->select();

        $doctorIdList = [];
        foreach ($doctorModelList as $item) {
            $doctorIdList[] = $item['doctor_id'];
        }
        // 查询咨询次数
        $serviceTimesField = [
            "type",
            "count(`type`) as c"
        ];
        foreach ($doctorModelList as $item) {
            $serviceTimes = Consultation::build()
                ->field($serviceTimesField)
                ->where("d_id", $item['doctor_id'])
                ->where("state", "已完成")
                ->where("type", "图文咨询")
                ->group('type')
                ->find();
            $item['image_service_times'] = $serviceTimes['c'] ?: 0;
        }

        return $doctorModelList;
    }

    /**
     * 获取医生详情
     * @param $did
     *
     * @return bool
     */
    public function getDetail($did)
    {
        $doctor = new Doctor();
        $whereQuery = [
            'doctor_id' => $did,
        ];

        $result = $doctor->where($whereQuery)->find();
        if (empty($result)) {
            return false;
        }
        $infoFields = [
            'doctor_id', 'nick_name', 'avatar', 'gender', 'age', 'province', 'city', 'title',
            'hospital', 'department_parent', 'department_child', 'department_phone',
            'follower_count', 'service_times', 'gift_times'
        ];
        $resultData = $result->visible($infoFields)->toArray();
        $conFields = [
            'is_open_image', 'is_open_phone', 'is_open_video' , 'is_open_private',
            'image_price', 'phone_price', 'video_price', 'private_price', 'ex_price',
        ];
        $extendFields = [
            'good_at', 'intro1', 'intro2', 'intro3', 'easemob_username', 'is_audited'
        ];
        $resultData['consultation'] = $result->visible($conFields, true)->toArray();
        $resultData['extend'] = $result->visible($extendFields, true)->toArray();
        $resultData['statistic'] = $result->visible(['statistic_good_at', 'statistic_impression'], true)->toArray();

        // 额外信息
        $extraData = [
            'comments' => [],
            'image_service_times' => 0,
            'phone_service_times' => 0,
            'video_service_times' => 0,
        ];
        $consultation = new Consultation();
        // 查询最新3条用户评价
        $commentsField = "con.con_id, con.c_id, con.grade, con.evaluation, con.comment_time, cus.nick_name, oa.oauth_type";
        $comments = $consultation->alias('con')->field($commentsField)
            ->join("__USER__ cus", "con.c_id = cus.user_id")
            ->join("__OAUTH__ oa", "oa.user_id = cus.user_id", "LEFT")
            ->where("con.d_id", $did)
            ->where("con.state", "已完成")
            ->where("con.evaluation", 'not null')
            ->where("con.evaluation", 'neq', '')
            ->order("con.comment_time DESC")
            ->limit(0, 3)->select();
        $goodCount = Consultation::build()->where(['d_id' => $did, 'state' => '已完成', 'grade' => '很满意'])->count();
        $normalCount = Consultation::build()->where(['d_id' => $did, 'state' => '已完成', 'grade' => '满意'])->count();
        $badCount = Consultation::build()->where(['d_id' => $did, 'state' => '已完成', 'grade' => '不满意'])->count();
        $extraData['grade_good_count'] = $goodCount;
        $extraData['grade_normal_count'] = $normalCount;
        $extraData['grade_bad_count'] = $badCount;
        $extraData['grade_total_count'] = $goodCount + $normalCount + $badCount;

        foreach ($comments as $item) {
            $oauthType = $item->oauth_type;
            if ($oauthType == 'wechat') {
                $item->oauth_type = '微信用户';
            } elseif ($oauthType == 'qq') {
                $item->oauth_type = 'QQ用户';
            } else {
                $item->oauth_type = '手机用户';
            }
            $extraData['comments'][] = $item->toArray();
        }

        // 查询咨询次数
        $serviceTimesField = [
            "type",
            "count(`type`) as c"
        ];
        $serviceTimes = $consultation->field($serviceTimesField)->where("d_id", $did)->where("state", "已完成")->group('type')->select();
        foreach ($serviceTimes as $item) {
            switch ($item->type) {
                case '图文咨询':
                    $extraData['image_service_times'] = $item->c;
                    break;
                case '电话咨询':
                    $extraData['phone_service_times'] = $item->c;
                    break;
                case '视频咨询':
                    $extraData['video_service_times'] = $item->c;
                    break;
            }
        }

        // 热门话题
        $postWhereMap = [
            'user_id' => $did,
            'is_doctor' => '1',
            'is_deleted' => 'no',
        ];
        $postField = [
            'post_id', 'post_type', 'group_type', 'title', 'is_top',
            'views_count', 'comments_count', 'create_time'
        ];
        $postModelList = CommPosts::build()
            ->field($postField)
            ->where($postWhereMap)
            ->order('create_time DESC')
            ->limit(0, 3)->select();

        return array_merge($resultData, $extraData, [ 'posts' => $postModelList ]);
    }

    public static function getPosts($doctorId, $pageIndex, $pageSize)
    {
        // 热门话题
        $postWhereMap = [
            'user_id' => $doctorId,
            'is_doctor' => '1',
            'is_deleted' => 'no',
        ];
        $postField = [
            'post_id', 'post_type', 'group_type', 'title', 'is_top', 'is_doctor',
            'views_count', 'comments_count', 'create_time'
        ];
        $postModelList = CommPosts::build()
            ->field($postField)
            ->where($postWhereMap)
            ->order('create_time DESC')
            ->page($pageIndex, $pageSize)->select();

        return $postModelList;
    }

    public function getComments($did, $page = 1, $num = 20)
    {
        try {
            $commentsField = [
                "con.con_id", "con.c_id", "con.grade", "con.impression", "con.evaluation", "con.comment_time",
                "cp.content", "cus.nick_name", "oa.oauth_type"
            ];

            $consultation = new Consultation();
            $comments = $consultation->alias('con')
                ->field($commentsField)
                ->join("__USER__ cus", "con.c_id = cus.user_id")
                ->join("__CONSULTATION_PROFILE__ cp", "con.con_id = cp.con_id", "LEFT")
                ->join("__OAUTH__ oa", "oa.user_id = cus.user_id", "LEFT")
                ->where("con.d_id", $did)
                ->where("con.state", "已完成")
                ->where("con.evaluation", 'not null')
                ->where("con.evaluation", 'neq', '')
                ->order("con.comment_time DESC")
                ->page($page, $num)
                ->select();

            $resultSet = [];
            foreach ($comments as $item) {
                $oauthType = $item->oauth_type;
                if ($oauthType == 'wechat') {
                    $item->oauth_type = '微信用户';
                } elseif ($oauthType == 'qq') {
                    $item->oauth_type = 'QQ用户';
                } else {
                    $item->oauth_type = '手机用户';
                }
                $resultSet[] = $item->toArray();
            }

            return $resultSet;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 通过'地区'和'科室'筛选医生
     * @param     $area1
     * @param     $area2
     * @param     $department1
     * @param     $department2
     * @param int $page
     * @param int $num
     * @param string $type
     * @return array
     */
    public function searchByAreaAndDepartment($area1, $area2, $department1, $department2, $page = 1, $num = 20, $type = 'image')
    {
        $page = "{$page}, {$num}";
        $order = 'do.service_times DESC';

        $whereQuery = [
            'do.area1' => ['like', "{$area1}%"],
            'do.area2' => ['like', "{$area2}%"],
            'de.department1' => ['like', "{$department1}%"],
            'de.department2' => ['like', "{$department2}%"],
            'de.is_default' => 'yes'
        ];
        if ($type != 'all') {
            $whereQuery['is_open_' . $type] = 'yes';
        }

        $doctor = new Doctor();

        $list = $doctor->audit()->alias('do')
            ->join("__DEPARTMENT__ de", 'de.d_id = do.doctor_id')
            ->where($whereQuery)
            ->order($order)->page($page)->select();

        $res = array();
        $showKeys = array_merge($this->getDoctorKeys(), $this->getDepartmentKeys());
        foreach ($list as $k => $v) {
            $res[] = $v->visible($showKeys)->toArray();
        }
        return $res;
    }

    /**
     * 通过'科室'筛选医生, 排除指定id
     * @param     $department
     * @param     $un_d_id
     * @return array
     */
    public function searchByDepartment($department, $un_d_id = '')
    {
        $where['department'] = $department;
        $ex_doctor = Db::name('ex_doctor')->where($where)->field("doctor_list")->find();
        if (empty($ex_doctor)) {
            return $ex_doctor;
        }
        $order = 'do.service_times DESC';
        $where = "de.is_default = 'yes' AND do.doctor_id in (" . $ex_doctor['doctor_list'] . ")";
        // 排除医生
        if (!empty($un_d_id)) {
            $where .= " AND do.doctor_id != $un_d_id";
        }
        $doctor = new Doctor();

        $list = $doctor->audit()->alias('do')
            ->join("__DEPARTMENT__ de", 'de.d_id = do.doctor_id')
            ->where($where)
            ->order($order)->select();

        $res = array();
        $showKeys = array_merge($this->getDoctorKeys(), $this->getDepartmentKeys());
        foreach ($list as $k => $v) {
            $res[] = $v->visible($showKeys)->toArray();
        }
        return $res;
    }

    /**
     * 验证医生ID
     * @param $did
     *
     * @return bool
     */
    public function validateId($did)
    {
        $doctor = new Doctor();
        $res = $doctor->audit()->field('doctor_id')->where('doctor_id', $did)->find();
        if (empty($res['doctor_id']) || $res['doctor_id'] == null) {
            return false;
        }
        return $res['doctor_id'];
    }

    public function getDocGoodAt($did)
    {
        $doctor = new Doctor();
        $whereQuery = [
            'doctor_id' => $did,
        ];
        $fieldStatement = [
            'doctor_id',
            'good_at'
        ];

        $result = $doctor->field($fieldStatement)->where($whereQuery)->find();

        if (empty($result)) {
            return false;
        }

        $goodAt = $result->good_at;
        $goodAt = explode('、', $goodAt); // why '、'? see Doctor.php at getGoodAtAttr()

        return $goodAt;
    }

    public function updateStatisticData($did, $data)
    {
        // 添加医生印象统计
        $doctor = new Doctor();
        $doctorData = $doctor->where(['doctor_id' => $did])->find();

        $goodAtArr = customUnserialize($doctorData->getData('statistic_good_at'));
        $impressionArr = customUnserialize($doctorData->getData('statistic_impression'));

        $newGoodAtArr = $this->countStatistic($goodAtArr, $data['doc_good_at']);
        $newImpressionArr = $this->countStatistic($impressionArr, $data['impression']);

        $newGoodAtStr = customSerialize($newGoodAtArr);
        $newImpressionStr = customSerialize($newImpressionArr);

        $updateData = [
            'statistic_good_at' => $newGoodAtStr,
            'statistic_impression' => $newImpressionStr,
        ];

        $result = $doctorData->save($updateData);

        return $result;

    }

    public function addServiceTimes($did)
    {
        try {
            $doctor = new Doctor();
            $result = $doctor->where("doctor_id", $did)->setInc("service_times");
            if ($result === false) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function valiSession($token)
    {
        $session = new DoctorApisession();
        $result = $session->where(['token' => $token, 'is_logout' => 'no'])->find();
        if (empty($result)) {
            return false;
        }

        // 验证有效期, 过期的话ban
        if ($result['expiry'] < time()) {
            $result->save(['is_logout' => 'yes']);
            return false;
        }

        // 再验一下id
        $did = Doctor::where('doctor_id', $result['doctor_id'])->value('doctor_id');
        if (empty($did)) {
            return false;
        }

        return $did;
    }

    public function getGiftList($did, $page = 1, $num = 20, $hideName = true)
    {
        try {
            $res = [];
            $gift = new Gift();
            $list = $gift->alias('gf')->field('gf.*, cu.nick_name, cu.mobile_num, cu.avatar')
                ->join("__CUSTOMER__ cu", "gf.c_id = cu.c_id")
                ->where('gf.d_id', $did)
                ->order('gf.create_time DESC')
                ->page($page, $num)
                ->select();
            foreach ($list as $item) {
                $res[] = $item->toArray();
            }
            foreach ($res as &$item) {
                $username = $item['nick_name'];
                if (empty_without_zero($username)) {
                    if (strlen($item['mobile_num']) == 11) {
                        $username = $item['mobile_num'];
                        $username[3] = $username[4] = $username[5] = $username[6] = '*';
                    }
                }
                if (empty($username)) {
                    $username = '微信用户';
                }
                $item['nick_name'] = $username;
                $item['mobile_num'] = '';
            }
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    private function countStatistic($originData, $addedData)
    {
        $addedArray = explode(SQL_SEPARATOR, $addedData);
        $addedArray = array_unique($addedArray);
        foreach ($addedArray as $item) {
            if (isset($originData[$item])) {
                $originData[$item] = ($originData[$item] + 1) . "";
            } else {
                $originData[$item] = "1";
            }
        }

        return $originData;
    }

}
