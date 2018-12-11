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
use app\admin\model\CommentModel;
use app\admin\model\PostModel;

use think\Db;
class Comment extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
		$map['c.is_deleted'] = 'no';
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['content'] = array('like', "%$search%");
            }
        }
		
        $count = CommentModel::build()->alias('c')->where($map)->count();
        // 获得列表数据
        $list = new CommentModel();
		$list = $list->alias('c')->join('yyb_user u','u.user_id = c.user_id', "LEFT")
		->join('yyb_comm_posts p','p.post_id = c.post_id', "LEFT")
		->where($map)
		->field(['c.*','nick_name','mobile','title'])
		->order('comment_id desc')
		->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['comment_id']));
        }
			
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('comment/index',['list' => $list,'count'=>$count]);

    }
	
	// 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
            $article = new CommentModel();
            $post = new PostModel();
            $flag = $article->add($param);
			
			$posts = $post->getOnePost($param['post_id']);
			$count = $posts['comments_count'] + 1;
			
			$info = db("comm_posts")->where('post_id', $param['post_id'])->setField('comments_count', $count);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new CommentModel();
        $flag = $article->delComment($id);
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
            '删除' => [
                'auth' => 'comment/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
