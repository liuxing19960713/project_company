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
use app\admin\model\ReportModel;
use app\admin\model\UserModel;
use app\admin\model\PostModel;
use app\admin\model\CommentModel;

use think\Db;
class Report extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
        $sName = input('searchName');
		$filter = input('filter');
		$map['is_deleted'] = 'no';
		$map['report_type'] = 'post';
		$titles = "帖子";
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
				if($search!='通过'){
                	$map['report_status'] = $search;
				}else{
                	$map['report_status'] = "";
				}
            }
			
			
			if($filter!=""){
				$query_arr = $query_arr + ['filter'=>$filter];
				if($filter=="评论举报"){
					$map['report_type'] = 'comment';
					$titles = "评论";
				}
			}
			
        }
		
        $count = ReportModel::whereCount($map);
        // 获得列表数据
        $list = new ReportModel();
		$list = $list->where($map)->order('report_id desc')->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			
			if($list[$k]['report_status']=="已处理"){
				$list[$k]['report_status'] = '<font color="#ec4758">已处理</font>';
			}else if($list[$k]['report_status']=="待处理"){
				$list[$k]['report_status'] = '<font color="#337ab7">待处理</font>';
			}else if($list[$k]['report_status']==""){
				$list[$k]['report_status'] = '<font color="#1ab394">通过</font>';
			}
			
			$list[$k]['operate'] = showOperate($this->makeButton($v['report_id']));
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('report/index',['list' => $list,'count'=>$count,'filter'=>$filter,'search'=>$search,'titles'=>$titles]);

    }
	
	
	public function Edit(Request $request = null)
    {
        $news = new ReportModel();
        if(request()->isPost()){

            $param = input('post.');
			
            $param['update_time'] = time();

			
			if ($param['report_status'] == '已处理') {
				$comment_bool = 'yes';
			} else {
				$comment_bool = 'no';
			}
			
			//被举报ID
            $tid = $request->param('id');
			
			if($param['report_type']=="post"){
				//帖子
				$tables = "comm_posts";
				$wheres = "post_id";
			}else{
				//评论
				$tables = "comm_comments";
				$wheres = "comment_id";
			}
			
			
			////根据ID隐藏帖子
			$info = db($tables)->where($wheres, $tid)->setField('is_deleted', $comment_bool);
	
			if ($info === false) {
				$this->setRenderMessage('被举报的评论不存在');
				return $this->getRenderJson();
			}
	
			$postModel = ReportModel::get($param['report_id']);
			$flag = $news->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
			//return $this->getRenderJson();
        }

        $id = input('param.id');
        $this->assign([
            'show' => $news->getOneReport($id)
        ]);
        return $this->fetch();
    }
	

    public function Del()
    {
        $id = input('param.id');

        $article = new ReportModel();
        $flag = $article->delReport($id);
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
			'审核' => [
                'auth' => 'report/edit',
                'href' => url('report/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'report/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}





