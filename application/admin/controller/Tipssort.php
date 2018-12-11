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
use app\admin\model\TipssortModel;

use think\Db;
class Tipssort extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
		
		
        $count = TipssortModel::whereCount($map);
        // 获得列表数据
        $list = new TipssortModel();
		$list = $list->where($map)->order('id desc')->paginate(10,false);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['id']));
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('tipssort/index',['list' => $list,'count'=>$count]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');

            $param['create_time'] = time();
			$param['content'] =  trim(input('content'));

            $tipssort = new TipssortModel();
            $flag = $tipssort->addTipssort($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function Edit()
    {
        $tipssort = new TipssortModel();
        if(request()->isPost()){

            $param = input('post.');
			
            //$param['create_time'] = time();
			$param['content'] =  trim(input('content'));
			
            $flag = $tipssort->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'show' => $tipssort->getOneTipssort($id)
        ]);
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $tipssort = new TipssortModel();
        $flag = $tipssort->delTipssort($id);
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
                'auth' => 'tipssort/edit',
                'href' => url('tipssort/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'tipssort/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
