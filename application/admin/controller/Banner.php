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
use app\admin\model\BannerModel;
use think\Db;
use app\appapi\model\AppIntModel;
//开放协议，允许外部跨域请求该资源，项目调试完成上线后必须关闭此功能！
header("Access-Control-Allow-Origin:*");

class Banner extends Base {
    //页面输出json格式数据
    protected function jsonReturn($data) {
        header('Content-Type:application/json');
        $returndata = json_encode($data);
        echo $returndata;
    }
	//轮播图添加接口（安卓、IOS双版本 轮播图）
	public function addsdk() {
		$table = "";
		$filter = $_POST['Add']['filter'];
		if($filter=="1") {
			$table = "yyb_banner_user";
		} else if($filter=="2") {
			$table = "yyb_banner_doctor";
		} else if($filter=="3") {
			$table = "yyb_banner_other";
		} 
		$return = AppIntModel::AddData($table,[],[
			'order_num' => $_POST['Add']['order'],
			'img_url' => $_POST['Add']['img_1'],
			'img_url2' => $_POST['Add']['img_2'],
			'href_url' => $_POST['Add']['href'],
		]);
		$this -> jsonReturn($return);
	}
	//轮播图修改接口（安卓、IOS双版本 轮播图）
	public function revisedsdk() {
		$table = "";
		$idname = "";
		$filter = $_POST['edit']['filter'];
		if($filter=="1") {
			$table = "yyb_banner_user";
			$idname = "bau_id";
		} else if($filter=="2") {
			$table = "yyb_banner_doctor";
			$idname = "bad_id";
		} else if($filter=="3") {
			$table = "yyb_banner_other";
			$idname = "id";
		} 
		$return = AppIntModel::UpData($table,[
			$idname => $_POST['edit']['id'],
		],[
			'order_num' => $_POST['edit']['order'],
			'img_url' => $_POST['edit']['img_1'],
			'img_url2' => $_POST['edit']['img_2'],
			'href_url' => $_POST['edit']['href'],
		]);
		$this -> jsonReturn($return);
	}
    // 文章列表
    public function index(Request $request = null) {
		$map = [];
        $query_arr = [];
		$filter = input('filter');
		if($filter=='') {
			$filter = 1;
		}
        if ($_GET) {
            if (!empty($filter)) {
                $query_arr = ['filter'=>$filter];
            }
        }
		//医生轮播图
		if ($filter==2) {
			$count = db('banner_doctor')->where($map)->count();
			
			// 获得列表数据
			
			$info_mod = Db::name('banner_doctor');

			$list = $info_mod->where($map)->order('order_num DESC')->paginate(10,false, ['query' => $query_arr]);
			$items = $list->items();
			
			foreach ($items as $k => $v){
				$items[$k]['operate'] = showOperate($this->makeButton($v['bad_id'],2));
				$items[$k]['ids'] = $v['bad_id'];
			}
			$this->assign('list', $items);
			$title = "医生轮播图列表";
			
		//其他轮播图
		} else if ($filter==3) {
			$count = db('banner_other')->where($map)->count();
			// 获得列表数据
			$info_mod = Db::name('banner_other');

			$list = $info_mod->where($map)->order('order_num DESC')->paginate(10,false, ['query' => $query_arr]);
			$items = $list->items();
			
			foreach ($items as $k => $v){
				$items[$k]['operate'] = showOperate($this->makeButton($v['id'],2));
				$items[$k]['ids'] = $v['id'];
			}
			$this->assign('list', $items);
			$title = "其他轮播图列表";
		} else {
			$count = BannerModel::whereCount($map);
			// 获得列表数据
			$list = new BannerModel();
			$list = $list->where($map)->order('order_num desc')->paginate(10,false, ['query' => $query_arr]);
			
			foreach($list as $k => $v){
				$list[$k]['operate'] = showOperate($this->makeButton($v['bau_id']));
				$list[$k]['ids'] = $v['bau_id'];
			}
			$this->assign('list', $list);
			$title = "用户轮播图列表";
		}
		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('banner/index',['count'=>$count,'filter'=>$filter,'title'=>$title]);

    }
	
	// 添加轮播图
    public function Add() {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = time();
			$banner = new BannerModel();
			if($param['filter']==2){
				$flag = db('banner_doctor')->insert($param);
			}else if($param['filter']==3){
				$flag = db('banner_other')->insert($param);
			}else{
            	$flag = $banner->add($param);
			}

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $filter = input('param.filter');
		if ($filter==1) {
			$title = "添加用户轮播图";
		} else if ($filter==2) {
			$title = "添加医生轮播图";
		} else if ($filter==3) {
			$title = "添加其他轮播图";
		}

        return $this->fetch('add', ['filter' => $filter,'title' => $title]);
    }
	
	public function Edit(Request $request = null) {
        $banner = new BannerModel();
        if(request()->isPost()){
			$param = input('post.');
			
			if (empty($param['id'])) {
			  $this->setRenderMessage('信息传递错误!');
			  return $this->getRenderJson();
			}
			
			if($param['filter']==2){
				$tid = $request->param('id');
				$flag = db('banner_doctor')->where('bad_id', $tid)->setField($param);
			}else if($param['filter']==3){
				$tid = $request->param('id');
				$flag = db('banner_other')->where('id', $tid)->setField($param);
			}else{
				$flag = $banner->edit($param);
			}
			
			return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $filter = input('param.filter');
		
		if($filter==2) {
        	$show = db('banner_doctor')->find($id);
			$ids = $show['bad_id'];
			$title = '编辑医生轮播图';
		}else if($filter==3){
        	$show = db('banner_other')->find($id);
			$ids = $show['id'];
			$title = '编辑其他轮播图';
		}else if($filter==1){
        	$show = db('banner_user')->find($id);
			$ids = $show['bau_id'];
			$title = '编辑用户轮播图';
		}
        return $this->fetch('edit', ['show' => $show,'ids' => $ids,'title' => $title,'filter' => $filter]);
    }
    public function Del() {
        $id = input('param.id');
        $filter = input('param.filter');
//		if($filter==2){
//			$flag = db('banner_doctor')->where('bad_id',$id)->delete();
//		}else{
//			$article = new BannerModel();
//			$flag = $article->delBanner($id);
//		}
		$article = new BannerModel();
		$flag = $article->delBanner($id,$filter);
		
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
	
	//是否隐藏轮播图
    public function isHidden() {
		$show = new BannerModel();
		$param['is_hidden'] = input('param.value');
		$param['id'] = input('param.id');
		$flag = $show->edit($param);
		return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$lx='') {
        return [
			'编辑' => [
                'auth' => 'Banner/edit',
                'href' => "javascript:Edit(" . $id . ")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'Banner/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}





