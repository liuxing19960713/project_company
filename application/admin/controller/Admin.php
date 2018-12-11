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

use app\admin\model\AdminModel;
use think\Request;
use think\Session;
use think\Db;
use think\View;

class Admin extends Base
{

    //用户列表
    public function index()
    {
		// 检测权限
		
		if(session('action') > 1){
			$this->error('403 您没有权限');
		}
		
		$map = [];
        $query_arr = [];
		$map['is_deleted'] = 'no';
        $search = input('searchText');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['account'] = array('like', "%$search%");
            }
        }
		
		
        $count = AdminModel::whereCount($map);
        // 获得列表数据
        $list = new AdminModel();
		$list = $list->where($map)->order('admin_id asc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
        	$list[$k]['last_login_time'] = $list[$k]['last_login_time']?date('Y-m-d H:i:s',$list[$k]['last_login_time']):'';
			
			if($list[$k]['admin_rank'] == 1){
            	$list[$k]['admin_rank'] = "超级管理员";
            }else if($list[$k]['admin_rank'] == 2){
            	$list[$k]['admin_rank'] = "普通管理员";
            }
			
			$list[$k]['operate'] = showOperate($this->makeButton($v['admin_id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('admin/index',['list' => $list,'count'=>$count]);
    }

    // 添加用户
    public function adminAdd()
    {
        if(request()->isPost()){

            $param = input('post.');

            $param['password'] = md5(md5($param['password']));
            $admin = new AdminModel();
            $flag = $admin->insertAdmin($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }


        return $this->fetch('admin/adminadd');
    }

    // 编辑用户
    public function adminEdit()
    {
        $admin = new AdminModel();

        if(request()->isPost()){

            $param = input('post.');

            if(empty($param['password'])){
                unset($param['password']);
            }else{
                $param['password'] = md5(md5($param['password']));
            }
			
            $flag = $admin->editAdmin($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        //$role = new RoleModel();
//
        $this->assign([
            'admin' => $admin->getOneAdmin($id),
            'status' => config('admin_status'),
            //'role' => $role->getRole()
        ]);
        return $this->fetch('admin/adminedit');
    }

    // 删除用户
    public function adminDel()
    {
        $id = input('param.id');
        $role = new AdminModel();
        $flag = $role->delAdmin($id);
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
            '编辑' => [
                'auth' => 'admin/adminedit',
                'href' => url('admin/adminEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'admin/admindel',
                'href' => "javascript:adminDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
