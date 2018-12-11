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
use app\admin\model\PostModel;

use think\Db;
class Post extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
		$map['p.is_deleted'] = 'no';
		$map['is_doctor'] = 0;
        $search = input('searchText');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
        }
		
		$count = PostModel::build()->alias('p')->join('yyb_user u','u.user_id = p.user_id')->where($map)->count();
        //$count = PostModel::whereCount($map);
        // 获得列表数据
        $list = new PostModel();
		$field =['post_id',
                'u.user_id',
                'u.nick_name',
                'u.mobile',
                'post_type',
                'group_type',
                'is_top',
                'is_best',
                'title',
                'views_count',
                'comments_count',
                'p.create_time',
                'p.is_deleted ',
                'is_doctor',
        ];
		$list = $list->alias('p')->join('yyb_user u','u.user_id = p.user_id')->field($field)->where($map)->order('post_id desc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['post_id']));
			
			if($list[$k]['post_type'] == 'normal') {
				$list[$k]['post_type'] = '普通帖';
			}else if($list[$k]['post_type'] == 'exp') {
				$list[$k]['post_type'] = '经验贴';
			}else{
				$list[$k]['post_type'] = '求助帖';
			}
			
			if($list[$k]['group_type'] == 1) {
				$list[$k]['group_type'] = '经验交流';
			}else if($list[$k]['group_type'] == 2) {
				$list[$k]['group_type'] = '备孕难题';
			}else if($list[$k]['group_type'] == 3) {
				$list[$k]['group_type'] = '孕期专区';
			}else if($list[$k]['group_type'] == 4) {
				$list[$k]['group_type'] = '试管顾问';
			}else{
				$list[$k]['group_type'] = '其他';
			}
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('post/index',['list' => $list,'count'=>$count]);

    }
	
	
	// 医生文章列表
    public function doctor(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
		$map['p.is_deleted'] = 'no';
		$map['is_doctor'] = 1;
        $search = input('searchText');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
        }
		
		
		$count = PostModel::build()->alias('p')->join('yyb_doctor u','u.doctor_id = p.user_id')->where($map)->count();
        // 获得列表数据
        $list = new PostModel();
		$field = [
            'post_id',
            'p.user_id',
            'u.nick_name',
            'u.mobile',
            'post_type',
            'group_type',
            'is_top',
            'is_best',
            'p.title',
            'views_count',
            'comments_count',
            'p.create_time',
            'p.is_deleted ',
            'is_doctor',
        ];
		$list = $list->alias('p')->join('yyb_doctor u','u.doctor_id = p.user_id')->field($field)->where($map)->order('post_id desc')->paginate(10,false, ['query' => $query_arr]);
		
       
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton2($v['post_id']));
			
			if($list[$k]['post_type'] == 'normal') {
				$list[$k]['post_type'] = '普通帖';
			}else if($list[$k]['post_type'] == 'exp') {
				$list[$k]['post_type'] = '经验贴';
			}else{
				$list[$k]['post_type'] = '求助帖';
			}
			
			if($list[$k]['group_type'] == 1) {
				$list[$k]['group_type'] = '经验交流';
			}else if($list[$k]['group_type'] == 2) {
				$list[$k]['group_type'] = '备孕难题';
			}else if($list[$k]['group_type'] == 3) {
				$list[$k]['group_type'] = '孕期专区';
			}else if($list[$k]['group_type'] == 4) {
				$list[$k]['group_type'] = '试管顾问';
			}else{
				$list[$k]['group_type'] = '其他';
			}
        }
			
        return $this->fetch('post/doctor',['list' => $list,'count'=>$count]);

    }
	
	

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $post = new PostModel();
            $flag = $post->add($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }
	
	public function Doctor_add()
    {
        if(request()->isPost()){
            $param = input('post.');
			
			$info = db('doctor')->find($param['user_id']);
			if (empty($info)) {
				$this->setRenderMessage('医生ID不存在!');
				return $this->getRenderJson();
			}

            $param['is_doctor'] = 1;
            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $post = new PostModel();
            $flag = $post->add($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }
	

    public function Edit()
    {
        $show = new PostModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $show->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
		
		$id = input('param.id');
        $this->assign([
            'show' => $show->getOnePost($id)
        ]);
        return $this->fetch();
    }

	
	public function Doctor_edit()
    {
        $show = new PostModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $show->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
		
		$id = input('param.id');
        $this->assign([
            'show' => $show->getOnePost($id)
        ]);
        return $this->fetch();
    }
	

    public function PostDel()
    {
        $id = input('param.id');

        $show = new PostModel();
        $flag = $show->delPost($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
	
	
		/**
     * @return mixed
     * description 会员信息
     */
    public function userInfo()
    {
        $id = get_post_value('id');
        $info = PostModel::get($id);
        $info->userInfo;
//        halt($info->toArray());
        return $this->fetch('userInfo', ['info' => $info]);
    }

	
	
	//是否置顶帖子
    public function is_top()
    {
		$show = new PostModel();
		$param['is_top'] = input('param.value');
		$param['id'] = input('param.id');
		$flag = $show->edit($param);
		return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    //是否置顶帖子
    public function isBest()
    {
		$show = new PostModel();
		$param['is_best'] = input('param.value');
		$param['id'] = input('param.id');
		$flag = $show->edit($param);
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
                'auth' => 'post/edit',
                'href' => url('post/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'post/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
	
    private function makeButton2($id)
    {
        return [
            '编辑' => [
                'auth' => 'post/doctor_edit',
                'href' => url('post/doctor_edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'post/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
