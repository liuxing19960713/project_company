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
use app\admin\model\ConreportModel;

use think\Db;
class Conreport extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
        $sName = input('searchName');
		$sStatus = input('searchStatus');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $doctorMap['nick_name'] = array('like', "%$search%");
                //$doctorMap['is_deleted'] = 'no';
                $doctorInfo = db('doctor')->where($doctorMap)->field('doctor_id')->select();
//                halt($doctorInfo);
                $ids = array_column($doctorInfo, 'doctor_id');
                $map['d_id'] = array('in',$ids);
            }
			
			
            if (!empty($sName)) {
                $query_arr = $query_arr + ['searchName'=>$sName];
                $userMap['nick_name'] = array('like', "%$sName%");
                //$userMap['is_deleted'] = 'no';
                $userInfo = db('user')->where($userMap)->field('user_id')->select();
//                halt($doctorInfo);
                $ids = array_column($userInfo, 'user_id');
                $map['user_id'] = array('in',$ids);
            }
			
			
            if (!empty($sStatus)) {
                $query_arr = $query_arr + ['searchStatus'=>$sStatus];
                $map['status'] = $sStatus;
            }
			
			
        }
		
		
        $count = ConreportModel::whereCount($map);
        // 获得列表数据
        $list = new ConreportModel();
		$list = $list->where($map)->order('cr_id desc')->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['cr_id']));
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('conreport/index',['list' => $list,'count'=>$count,'searchStatus'=>$sStatus]);

    }


    public function Edit()
    {
        $id = input('param.id');
		
		
		$info = db('consultation_report')->find($id);
        if (empty($info)) {
            $this->error('问诊报告ID不存在', 'ConReport/index');
        }

        $doctor_id = $info['d_id'];
        $user_id = $info['user_id'];//不需要基本上为零
        $con_id = $info['con_id'];
        $cp_id = $info['cp_id'];//不需要基本上为零

        $doctorInfo= db('doctor')->find($doctor_id);
        $userInfo = db('user')->find($user_id);
        $conInfo = db('consultation')->find($con_id);
		
        $conProfile = db('consultation_profile')->find($cp_id);
        $this->assign('doctorInfo', $doctorInfo);
        $this->assign('userInfo', $userInfo);
        $this->assign('conInfo', $conInfo);
        $this->assign('conProfile', $conProfile);

		
        return $this->fetch('conreport/edit', ['info' => $info]);
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new ConreportModel();
        $flag = $article->delConreport($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    /**
     * 拼装操作按钮
     * @param $cr_id
     * @return array
     */
    private function makeButton($id)
    {
        return [
			'查看' => [
                'auth' => 'conreport/edit',
                'href' => url('conreport/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'conreport/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
