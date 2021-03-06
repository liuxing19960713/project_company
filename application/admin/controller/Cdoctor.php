<?php
//医生模块
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Cdoctor extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $count = count($info = DB::table("yyb_cdoctor") ->paginate(6));
        return $this->fetch("Cdoctor/index",['info'=>$info,'count'=>$count,'title'=>'医生管理模块']);


    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        return $this->fetch("Cdoctor/add");
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        $data               =   $request->param();
        $data['addtime']    =   date('Y-m-d H:i:s',time());
        // var_dump($data);
        if (DB::table("yyb_cdoctor")->insert($data)) {
            return $this->success("操作ok","Cdoctor/index");
        }else{
            return $this->error("操作error","Cdoctor/create");
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $DoctorData   = Db::table("yyb_cdoctor")->where("id",$id)->find();
        $title        = "编辑医生";
        //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        // var_dump($info);
        return $this->fetch("Cdoctor/edit",['DoctorData'=>$DoctorData,'title'=>$title,'QnToken'=>$QnToken]);
    }

    public function Qiniu() {
        //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        echo $QnToken;
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //源数据
        $info = DB::table("yyb_cdoctor")-> where("id",$id) ->find();
        //去除多余的数据
        $data = $request-> except(['/admin/cdoctor/update','id']);
        if ($data['img_url']=="") {
            $data['img_uirl'] = $info['img_url'];

        }  
        $res = DB::table("yyb_cdoctor")->where("id",$id)->update($data);
        if ($res) {
            return "ok";
        }else{
            return "error";
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        // var_dump($id);
        if (DB::table("yyb_cdoctor")->where("id",$id)->delete()) {
            return "删除ok";
        }else{ 
            return "删除error";
        }
    }

    /**
     * 修改医生的状态
     */
    public function status(Request $request,$id)
    {
        // var_dump($id);
        $info = DB::table("yyb_cdoctor")-> where("id",$id)-> find();
        // var_dump($info);
        if ($info['status'] == 1) {
            // var_dump('1');
            Db::table("yyb_cdoctor")-> where("id", $id)-> update(['status'=>'0']);
            return '更改ok';
        }else{
            Db::table('yyb_cdoctor')-> where('id', $id)-> update(['status' => '1']);
            return '更改ok';
        }
       
    }

     
}
