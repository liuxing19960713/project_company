<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class InformationModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_consultation';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getInformationByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('con_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return InformationModel::where($dataMap)->count();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllInformation($where)
    {
        return $this->where($where)->count();
    }
	
	
	
	public function doctorInfo()
    {
        //关联查询多条数据
//        return $this->hasmany('Post', 'post_id', 'post_id')->where('is_deleted','no')->field(['post_id','title','content','create_time']);
        return $this->hasOne('Doctor', 'doctor_id', 'd_id')->where('is_deleted','no')->field(['doctor_id','nick_name','title','hospital','create_time']);
    }

    public static function selectCurrentCon($userId, $conType, $pageIndex, $pageSize)
    {
        $conField = [
            'con_id', 'c_id', 'd_id', 'cp_id',
            'state', 'type', 'money',
            'appoint_time', 'create_time', 'create_time + 1800 as end_time', 'total_time', 'valid_time',
        ];
        $whereMap = [
            'c_id' => $userId,
            'state' => '进行中',
            'type' => $conType,
            'service_id' => '0', // 非私人医生
        ];
        if ($conType == '电话咨询' || $conType == '视频咨询') {
            $whereMap['appoint_time'] = [ 'gt', time() - 60 * 30 ];
        }
        $orderMap = [ 'create_time' => 'desc' ];
        $consultationModelList = Consultation::build()
            ->field($conField)
            ->where($whereMap)
            ->order($orderMap)
            ->page($pageIndex, $pageSize)
            ->select();

        $consultationModelList = self::addDoctorAndProfileInfo($consultationModelList);

        return $consultationModelList;
    }

    public static function selectHistoryCon($userId, $conType, $pageIndex, $pageSize)
    {
        $conField = [
            'con_id', 'c_id', 'd_id', 'cp_id',
            'state', 'type', 'money',
            'appoint_time', 'create_time', 'create_time + total_time as end_time', 'total_time', 'valid_time',
            'grade', 'evaluation', 'comment_time',
        ];
        $whereMap = [
            'c_id' => $userId,
            'state' => [ 'in', [ '已完成', '已取消' ] ],
            'type' => $conType,
            'service_id' => '0', // 非私人医生
        ];
        $orderMap = [ 'create_time' => 'desc' ];
        $consultationModelList = Consultation::build()
            ->field($conField)
            ->where($whereMap)
            ->order($orderMap)
            ->page($pageIndex, $pageSize)
            ->select();

        $consultationModelList = self::addReportInfo($consultationModelList);
        $consultationModelList = self::addDoctorAndProfileInfo($consultationModelList);

        return $consultationModelList;
    }

    public static function selectConInService($userId, $serviceId, $conType, $pageIndex, $pageSize)
    {
        $conField = [
            'con_id', 'c_id', 'd_id', 'cp_id',
            'state', 'type', 'money',
            'appoint_time', 'create_time', 'create_time + total_time as end_time', 'total_time', 'valid_time',
            'grade', 'evaluation', 'comment_time',
        ];
        $whereMap = [
            'c_id' => $userId,
            'type' => $conType,
            'service_id' => $serviceId, // 非私人医生
        ];
        $orderMap = [ 'create_time' => 'desc' ];
        $consultationModelList = Consultation::build()
            ->field($conField)
            ->where($whereMap)
            ->order($orderMap)
            ->page($pageIndex, $pageSize)
            ->select();

        $consultationModelList = self::addReportInfo($consultationModelList);
        $consultationModelList = self::addDoctorAndProfileInfo($consultationModelList);

        return $consultationModelList;
    }

    public static function selectCurrentPrivate($userId, $pageIndex, $pageSize)
    {
        $conField = [
            'con_id', 'c_id', 'd_id', 'cp_id',
            'state', 'type', 'money', 'appoint_time', 'create_time', 'create_time + total_time as end_time',
            'service_id', 'service_endtime'
        ];
        $whereMap = [
            'c_id' => $userId,
            'state' => '进行中',
            'type' => '图文咨询',
            'service_endtime' => ['gt',time()],//todo:: 2017/12/13添加 过滤已到期私人医生
            'service_id' => ['neq', '0'], // 私人医生
        ];
        $orderMap = [ 'create_time' => 'desc' ];
        $consultationModelList = Consultation::build()
            ->field($conField)
            ->where($whereMap)
            ->order($orderMap)
            ->page($pageIndex, $pageSize)
            ->select();

        $consultationModelList = self::addDoctorAndProfileInfo($consultationModelList);
        $consultationModelList = self::addServiceInfo($consultationModelList);

        return $consultationModelList;
    }

    public static function selectHistoryPrivate($userId, $pageIndex, $pageSize)
    {
        $conField = [
            'con_id', 'c_id', 'd_id', 'cp_id',
            'state', 'type', 'money', 'appoint_time', 'create_time', 'create_time + total_time as end_time',
            'service_id', 'service_endtime',
            'grade', 'evaluation', 'comment_time',
        ];
        $whereMap = [
            'c_id' => $userId,
            'state' => [ 'in', ['已完成', '已取消' ] ],
            'type' => '图文咨询',
            'service_id' => ['neq', '0'], // 私人医生
        ];
        $orderMap = [ 'create_time' => 'desc' ];
        $consultationModelList = Consultation::build()
            ->field($conField)
            ->where($whereMap)
            ->order($orderMap)
            ->page($pageIndex, $pageSize)
            ->select();

        $consultationModelList = self::addReportInfo($consultationModelList);
        $consultationModelList = self::addDoctorAndProfileInfo($consultationModelList);
        $consultationModelList = self::addServiceInfo($consultationModelList);

        return $consultationModelList;
    }

    public static function addServiceInfo($consultationModelList)
    {
        $serviceFieldList = [
            'se_id', 'money', 'real_money', 'status', 'during', 'create_time', 'end_time'
        ];
        $serviceIdList = [];
        $serviceModelMap = [];

        foreach ($consultationModelList as $item) {
            $serviceIdList[] = $item['service_id'];
        }

        $serviceModelList = Service::build()->field($serviceFieldList)->select($serviceIdList);

        $duringNameMap = [
            '7' => '1周',
            '30' => '1个月',
            '90' => '3个月',
            '180' => '6个月',
            '360' => '12个月',
        ];
        foreach ($serviceModelList as $item) {
            $item['during_name'] = $duringNameMap[$item['during']];
            $serviceModelMap[$item['se_id']] = $item;
        }

        foreach ($consultationModelList as $item) {
            $item['service'] = $serviceModelMap[$item['service_id']];
        }

        return $consultationModelList;
    }

    public static function addReportInfo($consultationModelList)
    {
        $reportIdList = [];
        $reportModelMap = [];
        $conIdList = [];

        foreach ($consultationModelList as $item) {
            $conIdList[] = $item['con_id'];
        }

        $reportField = [ 'con_id', 'cr_id' ];
        $reportModelList = ConsultationReport::build()->field($reportField)
            ->where([ 'con_id' => ['in', $conIdList]])->select();
        foreach ($reportModelList as $item) {
            $reportModelMap[$item['con_id']] = $item;
        }
        foreach ($consultationModelList as $item) {
            $tempConId = $item['con_id'];
            if (isset($reportModelMap[$tempConId])) {
                $item['cr_id'] = $reportModelMap[$tempConId]['cr_id'];
            } else {
                $item['cr_id'] = '';
            }
        }

        return $consultationModelList;
    }

    public static function addDoctorAndProfileInfo($consultationModelList)
    {
        $doctorIdList = [];
        $doctorModelMap = [];
        $cpIdList = [];
        $cpModelMap = [];

        foreach ($consultationModelList as $item) {
            $doctorIdList[] = $item['d_id'];
            if ($item['cp_id'] != '0') {
                $cpIdList[] = $item['cp_id'];
            }
        }

        $doctorField = array_merge(Doctor::doctorMiniField(), ['easemob_username', 'department_child']);
        $doctorModelList = Doctor::build()->field($doctorField)->select($doctorIdList);
        foreach ($doctorModelList as $item) {
            $doctorModelMap[$item['doctor_id']] = $item;
        }

        $cpModelList = ConsultationProfile::build()->select($cpIdList);
        foreach ($cpModelList as $item) {
            $cpModelMap[$item['cp_id']] = $item;
        }

        foreach ($consultationModelList as $item) {
            if (isset($cpModelMap[$item['cp_id']])) {
                $item['profile'] = $cpModelMap[$item['cp_id']];
            } else {
                $item['profile'] = (object)[];
            }
            if (isset($doctorModelMap[$item['d_id']])) {
                $item['user_info'] = $doctorModelMap[$item['d_id']];
            } else {
                $item['user_info'] = (object)[];
            }
        }

        return $consultationModelList;
    }
	

}