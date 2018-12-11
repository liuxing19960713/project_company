<?php 
	//公司简介
	namespace app\admin\controller;
	use think\Db;
	use think\Request;
	use app\admin\model\AboutModel;
	class About extends Base
	{
		 //所有列表	
		 public function index()
		 {
		 	//总数、列表
		 	$count 	= count($info 	= DB::table("yyb_about")->paginate(10));
 			$title	= '公司简介';
		 	return $this->fetch('About/index',['info'=>$info,'title'=>$title,'count'=>$count]);
		 }

		 //添加文章 	
		 public function add()
		 {
		 	 
		 	return $this->fetch("About/add");
		 }

		 /**
		  * 处理文章数据
		  */
		 public function doAdd()
		 {
		 	$request = Request();
		 	//获取所有的数据
		 	$article = $request->param();

		 	$data	 = DB::table("yyb_about")->insert($article);
		 	if ($data) {
		 		return $this->success('添加ok',"about/index");
		 	}else{
		 		return $this->success('添加false',"about/add");
		 	}
		 }

		 /**
		  * redact编辑
		  */
		 public function redact($id)
		 {
		 	$info = DB::table("yyb_about")->where("id",$id)->find();
		 	// var_dump($info);
		 	return $this->fetch("about/redact",['info'=>$info]);

		 }

		 /**
		  * 处理编辑数据
		  */
		 public function doRedact()
		 {
		 	$request = Request();
		 	$data 	 = $request->param();
		 	$id 	 = $data['id'];
		 	unset($data['id']);
		 	if (DB::table("yyb_about")->where("id",$id)->update($data)) {
		 		return $this->success("ok","about/index");

			 }else{
			 	return $this->error("error","about/redact"); 
			 }

		 /**
		  * 删除操作
		  */
		 	}
		public function delete()
		{
		 	$request = Request();
		 	$id 	 = $request->only(['id']);
		 	$data 	 = db('about')->delete($id);
		 	
		 	if($data) {
		 		return ['code'=>1,'message'=>'操作完成'];
		 	}else{
		 		return ['code'=>-1,'message'=>'操作完成'];
		 	}

		}





		 
	}
?>