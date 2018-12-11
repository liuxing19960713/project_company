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
use app\admin\model\TipsModel;
use app\admin\model\TipssortModel;

use think\Db;
class Tips extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
        $searchtop = input('searchtop');
        $searchhot = input('searchhot');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
			
            if (!empty($searchtop)) {
                $query_arr = ['searchtop'=>$searchtop];
				$map['is_top'] = "1";
            }
			
            if (!empty($searchhot)) {
                $query_arr = ['searchhot'=>$searchhot];
				$map['is_hot'] = "1";
            }
        }
		
		
        $count = TipsModel::whereCount($map);
        // 获得列表数据
        $list = new TipsModel();
		$sort = new TipssortModel();
		$list = $list->where($map)->order('tip_id desc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['create_time'] = date("Y-m-d",$list[$k]['create_time']);
			$list[$k]['operate'] = showOperate($this->makeButton($v['tip_id']));
			$cate = $sort->getOneTipssort($list[$k]['cate']);
			$list[$k]['cate'] = $cate['title'];
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('tips/index',['list' => $list,'count'=>$count,'searchtop'=>$searchtop,'searchhot'=>$searchhot]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $tips = new TipsModel();
            $flag = $tips->addTips($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
		
		
		// 获得列表数据
		$list = new TipssortModel();
		$list = $list->getTipssortByWhere([],[],[]);

		return $this->fetch('tips/add',['list' => $list]);
    }

    public function Edit()
    {
        $tips = new TipsModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $tips->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
		// 获得列表数据
        $list = new TipssortModel();
		$list = $list->getTipssortByWhere([],[],[]);
		
        $id = input('param.id');
        $this->assign([
            'show' => $tips->getOneTips($id),
            'list' => $list
        ]);
		
		
		
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $tips = new TipsModel();
        $flag = $tips->delTips($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
	
	public function is_top()
    {
		$show = new TipsModel();
		$param['is_top'] = input('param.value');
		$param['id'] = input('param.id');
		$flag = $show->edit($param);
		return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
	
	
	public function is_hot()
    {
		$show = new TipsModel();
		$param['is_hot'] = input('param.value');
		$param['id'] = input('param.id');
		$flag = $show->edit($param);
		return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
	
	
	public function Updata()
    {
		$news = new TipsModel();
		
		$where = [
			'tip_id' => $_POST['tip_id']
		];
		
		$UpData = [];
        $UpData['tip_id'] = $_POST['tip_id'];
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
                'auth' => 'tips/edit',
                'href' => url('tips/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '修改' => [
                'auth' => 'tips/updata',
                'href' => "javascript:AlterData(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-edit'
            ],
            '删除' => [
                'auth' => 'tips/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
