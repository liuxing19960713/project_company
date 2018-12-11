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
use app\admin\model\AppointModel;

use think\Db;
class Appoint extends Base
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
                $map['c_id'] = array('in',$ids);
            }
			
			
            if (!empty($sStatus)) {
                $query_arr = $query_arr + ['searchStatus'=>$sStatus];
                $map['status'] = $sStatus;
            }
			
			
        }
		
		
        $count = AppointModel::whereCount($map);
        // 获得列表数据
        $list = new AppointModel();
		$list = $list->where($map)->order('ap_id desc')->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['ap_id']));
			
			if($list[$k]['status']=="yes"){
				$list[$k]['status'] = '<font color="#1ab394">预约成功</font>';
			}else if($list[$k]['status']=="no"){
				$list[$k]['status'] = '<font color="#ec4758">预约失败</font>';
			}else if($list[$k]['status']=="wait"){
				$list[$k]['status'] = '<font color="#337ab7">预约中</font>';
			}else if($list[$k]['status']=="end"){
				$list[$k]['status'] = '<font color="#293846">预约结束</font>';
			}
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('appoint/index',['list' => $list,'count'=>$count,'searchStatus'=>$sStatus]);

    }


    public function Edit()
    {
        $id = input('param.id');
        //医生ID  用户ID;   咨询id    病历id consultation
        $info = db('appointment')
                ->alias('app')
                ->where('app.ap_id', $id)
                ->join('__USER__ cu ', 'app.c_id= cu.user_id', "LEFT")
                ->join('__DOCTOR__ do ', 'app.c_id= do.doctor_id', "LEFT")
                ->join('__CONSULTATION_PROFILE__ con ', 'app.cp_id= con.cp_id', "LEFT")
                ->find();
//        halt($info);

        if (empty($info)) {
            $this->error('信息错误', 'appointment/index');
        }
		
        return $this->fetch('appoint/edit', ['info' => $info]);
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new AppointModel();
        $flag = $article->delAppoint($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    /**
     * 拼装操作按钮
     * @param $ap_id
     * @return array
     */
    private function makeButton($id)
    {
        return [
			'查看' => [
                'auth' => 'appoint/edit',
                'href' => url('appoint/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'appoint/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
