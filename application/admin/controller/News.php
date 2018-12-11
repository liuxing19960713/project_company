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
use app\admin\model\NewsModel;

use think\Db;
class News extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
        }
		
		
        $count = NewsModel::whereCount($map);
        // 获得列表数据
        $list = new NewsModel();
		$list = $list->where($map)->order('an_id desc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['create_time'] = date("Y-m-d",$list[$k]['create_time']);
			$list[$k]['operate'] = showOperate($this->makeButton($v['an_id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('news/index',['list' => $list,'count'=>$count]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $news = new NewsModel();
            $flag = $news->addNews($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function Edit()
    {
        $news = new NewsModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $news->editNews($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'show' => $news->getOneNews($id)
        ]);
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $news = new NewsModel();
        $flag = $news->delNews($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function Updata()
    {
		$news = new NewsModel();
		
		$where = [
			'an_id' => $_POST['an_id']
		];
		
		$UpData = [];
        $UpData['an_id'] = $_POST['an_id'];
        $UpData['create_time'] =  strtotime($_POST['create_time']);
		
		$news -> AlterData($where,$UpData);
		
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
                'auth' => 'news/edit',
                'href' => url('news/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '修改' => [
                'auth' => 'news/updata',
                'href' => "javascript:AlterData(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-edit'
            ],
            '删除' => [
                'auth' => 'news/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
