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
namespace app\huod\controller;

use think\Controller;

class Index extends Controller {
   public function index() {
	    $name = input('param.name');
	    $openid = input('param.openid');
	    $imageTX = input('param.imageTX');
        return $this->fetch('index/index',['name' => $name,'openid'=>$openid,'imageTX'=>$imageTX]);
    }
	
	
	// 添加文章
    public function Add() {
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
}
