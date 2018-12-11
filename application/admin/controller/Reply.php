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
use app\admin\model\ReplyModel;

use think\Db;
class Reply extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
		$map['is_deleted'] = 'no';
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['content'] = array('like', "%$search%");
            }
        }
		
        $count = ReplyModel::whereCount($map);
        // 获得列表数据
        $list = new ReplyModel();
		$list = $list->alias('c')->where($map)->order('reply_id desc')->paginate(20,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['reply_id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('reply/index',['list' => $list,'count'=>$count]);

    }

    public function Del()
    {
        $id = input('param.id');

        $article = new ReplyModel();
        $flag = $article->delReply($id);
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
                'auth' => 'reply/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
