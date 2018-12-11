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
use app\admin\model\IncreaseModel;

use think\Db;
class Increase extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
		//$map['is_deleted'] = "no";
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
        }
		
		
        $count = IncreaseModel::whereCount($map);
        // 获得列表数据
        $list = new IncreaseModel();
		$list = $list->where($map)->order('in_id desc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['in_id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('increase/index',['list' => $list,'count'=>$count]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $article = new IncreaseModel();
            $flag = $article->add($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }
	
	

    public function Edit()
    {
        $article = new IncreaseModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $article->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'show' => $article->getOneIncrease($id)
        ]);
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new IncreaseModel();
        $flag = $article->delIncrease($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

	
    public function is_top()
    {
		$show = new IncreaseModel();
		$param['is_top'] = input('param.value');
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
                'auth' => 'increase/edit',
                'href' => url('increase/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'increase/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
