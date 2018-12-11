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
use app\admin\model\UserModel;
use app\admin\validate\UserValidate;

use think\Db;

class User extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
		$map['is_deleted'] = 'no';
        $search = input('searchText');
        $searchStatus = input('searchStatus');
		$order = 'user_id desc';
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['nick_name'] = array('like', "%$search%");
            }
            if (!empty($searchStatus)) {
                $query_arr = ['searchStatus'=>$searchStatus];
				$order = 'all_sign desc';
            }
        }
		
		
        $count = UserModel::build()->where($map)->count();
        //$count = UserModel::whereCount($map);
		
        $field = ['user_id,nick_name,mobile,create_time,app_store,avatar,all_sign,sign_time,remarks'];
        // 获得列表数据
        $list = new UserModel();
		$list = $list
		->where($map)
        ->field($field)
		->order($order)
		->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['user_id']));
			
//			if($list[$k]['oauth_type'] == 'wechat') {
//				$list[$k]['oauth_type'] = '<a style="color:#1ab394" title="微信"><i class="fa fa-wechat fa-2x"></i></a>';
//			}else if($list[$k]['oauth_type'] == 'qq') {
//				$list[$k]['oauth_type'] = '<a style="color:#ec4758" title="QQ"><i class="fa fa-qq fa-2x"></i></a>';
//			}else{
//				$list[$k]['oauth_type'] = '<a style="color:#337ab7" title="手机注册"><i class="fa fa-mobile fa-2x"></i></a>';
//			}
			
        }
		
			
		//打印语句
		//echo Db::getLastSql();
		
        return $this->fetch('user/index',['list' => $list,'count'=>$count,'search'=>$search,'searchStatus'=>$searchStatus]);

    }

    // 添加文章
    public function userAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
			
			if($this->validate(['mobile'=>$param['mobile']],'User.mobile') !== true){
				$this->setRenderMessage($this->validate(['mobile'=>$param['mobile']],'User.mobile'));
				return $this->getRenderJson();
			}
			
			$mobileInfo = db('user')->where(['mobile'=>$param['mobile'],'is_deleted'=>'no'])->select();
			if (!empty($mobileInfo)) {
				$this->setRenderMessage('此手机号码已经被注册过了');
				return $this->getRenderJson();
			}

			$param['password'] =  md5($param['password']);
			$param['create_time'] = time();
			
			
			$UserInfo = UserModel::create($param);
			if (empty($UserInfo)) {
				Db::rollback();
				$this->setRenderMessage('新增用户失败');
				return $this->getRenderJson();
			}
            //$article = new UserModel();
//            $flag = $article->add($param);
//			
//			$userId = Db::getLastInsID();
			
			// 添加试管记录
            //$tube = new TubeRecord();
			//$sg = $tube->initStage($userId);
	
			//获取环信账号
			$easemobAccount = UserModel::build()->addEaseMobAccount($UserInfo['user_id']);
	
			if (empty($easemobAccount)) {
				Db::rollback();
				$this->setRenderMessage('获取环信账号为空');
				return $this->getRenderJson();
			}
			
			$easeMobData = [
				'easemob_username' => $easemobAccount['username'],
				'easemob_password' => $easemobAccount['password'],
			];
			
			
        	$newInfo = db('user')->where(['user_id'=>$UserInfo['user_id']])->update($easeMobData);
			//$newInfo = $article-> where("user_id=$userId")->setField($easeMobData);
			
			if ($newInfo) {
				Db::commit();
				$this->setRenderSuccess();
				$this->setRenderResult($newInfo);
			} else {
				Db::rollback();
				$this->setRenderMessage('新增失败');
			}
			

        	return $this->getRenderJson();
            //return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function userEdit()
    {
        $article = new UserModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
        	$param['birthday'] = strtotime(input('birthday'));
			
            $flag = $article->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'article' => $article->getOneUser($id),
            //'oauth' => db('oauth')->where(['user_id'=>$id])->field(['oauth_type'])->find(),
			
			//连接试管记录表，把信息反馈在用户详情页面
			'tube' => db('tube_record')->where(['user_id'=>$id])->select()
        ]);
		
		
        return $this->fetch();
    }

    public function userDel()
    {
        $id = input('param.id');

        $article = new UserModel();
        $flag = $article->delUser($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * @return mixed
     * description 用户病历表user_details 表
     */
    public function user_Details()
    {
        $id = input('id');

        $show = db('user')
                ->alias('co')
                ->join('__USER_DETAIL__ do ', 'co.user_id= do.user_id', "LEFT")
                ->find($id);

        return $this->fetch('user/user_details', ['show'=>$show]);
    }
	
	
	
	/**
     * @return mixed
     * description 个人病历问诊报告详细信息内容
     */
    public function consultation()
    {
        $id = input('id');
        $userInfo = db('user')->find($id);
        if (empty($userInfo)) {
            $this->error('用户信息不存在', url('User/useredit', ['id'=>$id]), '', '2');
        }

        //问诊表详细信息
        $conInfo = db('consultation')->where('c_id', $id)
                ->order('create_time desc')
                ->limit(0, 1)
                ->find();
//        halt($conInfo);
        if (empty($conInfo)) {
            $this->error('此用户还没有问诊报告', url('User/useredit', ['id'=>$id]), '', '2');
        }

        $dtime= $conInfo['total_time'];
        $timedata = '';
        $h = floor($dtime%(3600*24)/3600);
        if ($h) {
            $timedata .= $h . "小时 ";
        }
        $m = floor($dtime%(3600*24)%3600/60);
        if ($m) {
            $timedata .= $m . "分钟 ";
        }
        $conInfo['total'] = $timedata; //计算出总的咨询时间
//        halt($conInfo['total']);
        //医生个人详细信息
//        halt($conInfo);
//        exit;
        $doctor_id = $conInfo['d_id'];
        $doctorInfo= db('doctor')->find($doctor_id);
        if (empty($doctorInfo)) {
            $this->error('医生信息不存在', 'User/useredit');
        }

        //问诊报告表详细信息
        $conProfile = db('consultation_profile')
                ->where('c_id', $id)
                ->order('cp_id desc')
                ->limit(0, 1)->find();
//        halt($conProfile);
        $con_id = $conProfile['con_id'];

        $show = db('consultation_report')
                ->where('user_id', $id)
                ->where('d_id', $doctor_id)
                ->where('con_id', $con_id)
                ->find($id);

        $this->assign('doctorInfo', $doctorInfo);
        $this->assign('userInfo', $userInfo);
        $this->assign('conInfo', $conInfo);
        $this->assign('conProfile', $conProfile);
        return $this->fetch('user/consultation', ['show'=>$show]);
    }
	
	
    public function Updata()
    {
		$data = new UserModel();
		
		$where = [
			'user_id' => $_POST['user_id']
		];
		
		$UpData = [];
        $UpData['user_id'] = $_POST['user_id'];
        $UpData['remarks'] = $_POST['remarks'];
		
		$data -> AlterData($where,$UpData);
		
		print_r("修改成功");
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
                'auth' => 'user/useredit',
                'href' => url('user/useredit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '修改' => [
                'auth' => 'user/updata',
                'href' => "javascript:AlterData(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-edit'
            ],
            '删除' => [
                'auth' => 'user/userdel',
                'href' => "javascript:userDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
