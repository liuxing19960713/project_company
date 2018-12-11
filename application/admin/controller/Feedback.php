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
use app\admin\model\FeedbackModel;

use think\Db;
class Feedback extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $map['member_type'] = 'customer';
        $sName = input('searchName');
        $search = input('searchText');
		$filter = input('filter');
		$joinuser = 'yyb_user u';
		$joinid = 'u.user_id = f.member_id';
		$Name = '用户名字';
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['content'] = array('like', "%$search%");
            }
			
            if (!empty($sName)) {
                $query_arr = $query_arr + ['searchName'=>$sName];
                $map['u.nick_name'] = array('like', "%$sName%");
            }
			
			if($filter!=""){
				$query_arr = $query_arr + ['filter'=>$filter];
				$map['member_type'] = $filter;
				
				if($filter == "doctor"){
					$joinuser = 'yyb_doctor u';
					$joinid = 'u.doctor_id = f.member_id';
					$Name = '医生名字';
				}
			}
			
        }
		
		
        $count = FeedbackModel::build()->alias('f')->join($joinuser,$joinid)->where($map)->count();
        // 获得列表数据
        $list = new FeedbackModel();
		$list = $list->alias('f')->join($joinuser,$joinid)
		->where($map)
		->field(['f.*','u.nick_name','u.mobile'])
		->order('id desc')
		->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('feedback/index',['list' => $list,'count'=>$count,'filter'=>$filter,'Name'=>$Name]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $article = new FeedbackModel();
            $flag = $article->add($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }
	
	

    public function Edit()
    {
        $article = new FeedbackModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $article->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $filter = input('filter');
		$joinuser = 'yyb_user u';
		$joinid = 'u.user_id = f.member_id';
				
		if($filter == "doctor"){
			$joinuser = 'yyb_doctor u';
			$joinid = 'u.doctor_id = f.member_id';
		}else if($filter == "customer"){
			$joinuser = 'yyb_user u';
			$joinid = 'u.user_id = f.member_id';
		}
		
		$where = [];
        $where['f.id'] = $id;
		
        $this->assign([
            'show' => $article->getOneFeedback($id,$joinuser,$joinid),
            'list' => $article->getAllFeedback($where,'yyb_feedback_img i','i.fb_id = f.id')
        ]);
		
//		$list = $article->alias('f')->join('yyb_feedback_img i','i.fb_id = f.id')
//		->field(['i.img_url'])
//		->order('i.id desc');
//		
//		$this->assign('list', $list);
		
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new FeedbackModel();
        $flag = $article->delFeedback($id);
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
                'auth' => 'feedback/edit',
                'href' => "javascript:Edit(" . $id . ")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'feedback/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
