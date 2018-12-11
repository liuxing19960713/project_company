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
namespace app\admin\controller;

use think\Request;
use app\admin\model\ConsultationModel;
use app\admin\model\DoctorModel;
use app\admin\model\UserModel;
use app\admin\model\ProfileModel;
use app\admin\model\ConsultationReportModel;

use think\Db;
class Consultation extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
		$Name = '用户名字';
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
				
				$doctorMap['nick_name'] = array('like', "%$search%");
                //$doctorMap['is_deleted'] = 'no';
                $doctorInfo = db('doctor')->where($doctorMap)->field('doctor_id')->select();
//                halt($doctorInfo);
                $ids = array_column($doctorInfo, 'doctor_id');
                $map['con.d_id'] = array('in',$ids);
            }
        }
		
		
        $count = db('consultation')
            ->alias('con')
            ->where($map)
            ->join('__DOCTOR__ do ', 'con.d_id= do.doctor_id', "LEFT")
            ->join('__USER__ cu ', 'con.c_id= cu.user_id', "LEFT")
            ->count();
        $field = [
                'con.con_id', 'con.d_id', 'con.c_id', 'con.create_time',
                'con.state','con.evaluation',
                'cu.nick_name as user_name','do.nick_name as doctor_name',
        ];
		
        $list = new ConsultationModel();
		$list = $list->alias('con')
		->where($map)
		->field($field)
        ->join('__DOCTOR__ do ', 'con.d_id= do.doctor_id', "LEFT")
        ->join('__USER__ cu ', 'con.c_id= cu.user_id', "LEFT")
        ->order('con_id DESC')
		->paginate(20,false, ['query' => $query_arr]);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['con_id']));
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('consultation/index',['list' => $list,'count'=>$count]);

    }


    public function Edit()
    {

        $consultation = new ConsultationModel();
		$userInfo = new UserModel();
		$info = new ProfileModel();
        $id = input('param.id');
		
		
		$list = $consultation->getOneConsultation($id);

        $dtime= $list['total_time'];
        $timedata = '';
        $d = floor($dtime%(3600*24));
        if ($d) {
            $timedata .= $d . "天 ";
        }
        $h = floor($dtime%(3600*24)/3600);
        if ($h) {
            $timedata .= $h . "小时 ";
        }
        $m = floor($dtime%(3600*24)%3600/60);
        if ($m) {
            $timedata .= $m . "分钟 ";
        }
        $list['total'] = $timedata; //计算出总的咨询时间
        $doctorInfo = DoctorModel::build()
                ->field(['doctor_id, nick_name, title, hospital'])
                ->find($list['d_id'])
                ->toArray();
        $userInfo = $userInfo->getOneUser($list['c_id']);
        $info = $info->getOneProfile($list['cp_id']);
        $reportInfo = ConsultationReportModel::build()->where(['con_id'=>$id, 'd_id'=>$list['d_id'], 'user_id'=>$list['c_id']])
                ->find();
        return $this->fetch('consultation/edit', ['list'=>$list, 'doctorInfo'=>$doctorInfo,
                'userInfo'=>$userInfo, 'info'=>$info, 'reportInfo'=>$reportInfo]);
		
		
		
		$this->assign([
            'show' => $consultation->getOneConsultation($id)
			//'userInfo' => $userInfo->getOneUser($show['c_id'])
        ]);
		
		$userInfo=getOneUser($show['c_id']);
		
        return $this->fetch();
        
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new ConsultationModel();
        $flag = $article->delConsultation($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
			'查看' => [
                'auth' => 'consultation/edit',
                'href' => url('consultation/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'consultation/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
