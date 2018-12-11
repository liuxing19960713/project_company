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
use app\admin\model\WithdrawModel;
use app\admin\model\UserModel;
use app\admin\model\DoctorModel;
use app\admin\model\MessageDoctorModel;
use app\admin\model\MessageUserModel;

use think\Db;
class Withdraw extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		$map = [];
        $query_arr = [];
        $map['user_type'] = 'customer';
        $search = input('searchText');
        $status = input('status');
        if ($_GET) {
            if (!empty($search)) {
                $map['u.nick_name'] = array('like', "%$search%");
            }
			
            if (!empty($status)) {
                $map['status'] = $status;
            }
        }
		
		
        $count = WithdrawModel::build()->alias('w')->join('yyb_user u','u.user_id = w.user_id')->where($map)->count();
        // 获得列表数据
        $list = new WithdrawModel();
        $list = $list->alias('w')->join('yyb_user u','u.user_id = w.user_id')->where($map)
                ->field(['w.*','u.nick_name','u.mobile'])
                ->order('wd_id DESC')
                ->paginate(20,false, ['query' => array('searchText' => $search, 'status' => $status)]);
				
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['wd_id']));
			
			if($list[$k]['status'] == 'yes') {
				$list[$k]['status'] = '<a style="color:#1ab394" title="提现成功"><i class="fa fa-check fa-2x"></i></a>';
			}else if($list[$k]['status'] == 'no') {
				$list[$k]['status'] = '<a style="color:#ec4758" title="提现失败"><i class="fa fa-close fa-2x"></i></a>';
			}else if($list[$k]['status'] == 'wait') {
				$list[$k]['status'] = '<a style="color:#337ab7" title="等待处理"><i class="fa fa-question fa-2x"></i></a>';
			}
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('withdraw/index',['list' => $list,'count'=>$count]);

    }
	
	
	/**
     * @param Request|null $request
     * @return mixed
     * description 医生提现列表页面
     */
    public function doctor(Request $request = null)
    {
		$map = [];
        $map['user_type'] = 'doctor';
        $search = input('searchText');
        $status = input('status');
        if ($_GET) {
            if (!empty($nick_name)) {
                $map['u.nick_name'] = array('like', "%$nick_name%");
            }
//            halt($gender);
            if (!empty($status)) {
                $map['status'] = $status;
            }

        }

        $count = WithdrawModel::build()->alias('w')->join('yyb_doctor u','u.doctor_id = w.user_id')->where($map)->count();
        // 获得列表数据
        $list = new WithdrawModel();
		
        $list = $list->alias('w')->join('yyb_doctor u','u.doctor_id = w.user_id')->where($map)
            ->field(['w.*','u.nick_name','u.mobile'])
            ->order('wd_id DESC')
            ->paginate(20,false, ['query' => array('searchText' => $search, 'status' => $status)]);
			
			
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton2($v['wd_id']));
			
			if($list[$k]['status'] == 'yes') {
				$list[$k]['status'] = '<a style="color:#1ab394" title="提现成功"><i class="fa fa-check fa-2x"></i></a>';
			}else if($list[$k]['status'] == 'no') {
				$list[$k]['status'] = '<a style="color:#ec4758" title="提现失败"><i class="fa fa-close fa-2x"></i></a>';
			}else if($list[$k]['status'] == 'wait') {
				$list[$k]['status'] = '<a style="color:#337ab7" title="等待处理"><i class="fa fa-question fa-2x"></i></a>';
			}
        }	
			
		//echo Db::getLastSql();	
        return $this->fetch('withdraw/doctor', ['list'=>$list, 'count'=>$count]);
    }
	
	

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $withdraw = new WithdrawModel();
            $flag = $withdraw->add($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function Edit()
    {
        $article = new WithdrawModel();
        if(request()->isPost()){

            $param = input('post.');
            $flag = $article->edit($param);
			
			$postModel = WithdrawModel::get($param['id']);
		
			if ($param['status'] == 'yes') {
				$text = '您的提现审核已通过';
			} else {
				$text = '您的提现审核被拒绝，原因：' . $param['reason'];
			}
	
			if ($postModel['user_type'] == 'customer') {
				MessageUserModel::pushSystemMessage(
					$postModel['user_id'],
					$text,
					[ 'event_type' => '提现', 'event_id' => $param['id'], 'sub_type' => '提现' ]
				);
			} else {
				$doctorModel = Doctor::get($postModel['user_id']);
				MessageDoctorModel::pushSystemMessage(
					$postModel['user_id'],
					$text,
					[ 'event_type' => '提现', 'event_id' => $param['id'], 'sub_type' => '提现' ],
					'0',
					$param['id'],
					'system',
					$doctorModel['easemob_username']
				);
			}

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
			'article' => $article->getOneUser($id)
//        halt($info->toArray());
        ]);

        return $this->fetch();
    }
	
	
	public function edit_doctor()
    {
        $article = new WithdrawModel();
        if(request()->isPost()){

            $param = input('post.');
            $flag = $article->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
			'article' => $article->getOneUser($id)
//        halt($info->toArray());
        ]);
        return $this->fetch();
    }
	
	
	/**
     * @return mixed
     * description 个人提现的详细信息
     */
    public function userInfo()
    {
        $id = get_post_value('id');
        $info = WithdrawModel::get($id);
        $info->userInfo;
//        halt($info->toArray());
        return $this->fetch('userInfo', ['info' => $info]);
    }
	
	
	    /**
     * @return mixed
     * description 医生个人提现的详细信息
     */
    public function doctorInfo()
    {
        $id = get_post_value('id');
        $info = WithdrawModel::get($id);
        $info->doctorInfo;
        return $this->fetch('doctorInfo', ['info' => $info]);
    }
	
	

    public function WithdrawDel()
    {
        $id = input('param.id');

        $article = new WithdrawModel();
        $flag = $article->delWithdraw($id);
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
                'auth' => 'withdraw/articleedit',
                'href' => url('withdraw/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'withdraw/articledel',
                'href' => "javascript:articleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
	
    private function makeButton2($id)
    {
        return [
            '编辑' => [
                'auth' => 'withdraw/articleedit',
                'href' => url('withdraw/edit_doctor', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'withdraw/articledel',
                'href' => "javascript:articleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
