<?php
//广告图管理
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Advert extends Base
{
    /**
     *  显示资源列表
     *  关联表：yyb_newssort
     * @return \think\Response
     */
    public function index()
    {
        //总数、数据、分页（6）
        $count  = count($info   = DB::table("yyb_advert")->alias('a') ->join('yyb_newssort n' , 'a.colum_id = n.id')->field('a.ad_name,a.img_url as ad_img,a.id as a_id,a.addtime,n.title') -> paginate(6));
        $title  = "广告列表";
        return $this -> fetch("Advert/index",['title'=>$title,'count'=>$count,'info'=>$info]); 
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //栏目数据
        $colum = db("newssort") -> where("shown",1)->select();
        //标题
        $title = "广告图的添加";
        return $this -> fetch("Advert/create",['colum'=>$colum,'title'=>$title]);

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
        $res            = $request -> param();
        // var_dump($res);
        $res['addtime'] = date('Y-m-d H:i:s',time());
        // var_dump($res);
        if (db("advert") -> insert($res)) {
            return "ok";
        } else {
            return "error";
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
        //栏目数据
        $colum = db("newssort") ->   where("shown",1) -> select();

        $info  = db("advert")   ->   where("id",$id)  -> find();
        $title = "广告图编辑";

        //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        //分配数据
        return $this -> fetch("Advert/alert",['colum'=>$colum,'info'=>$info,'title'=>$title,'QnToken'=>$QnToken]);
    }
    //七牛
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
        //
        $info   = db("advert") -> where("id",$id) ->find();
        $res    = $request -> except(['id','/admin/advert/update']);
        if ($res['img_url'] == "") {
            $res['img_url'] = $info['img_url'];
        }
        // var_dump($res);
        $result = db("advert") -> where("id" ,$id) ->update($res);
        if ($result) {
             return "编辑ok";
         } else {
            return "编辑error";
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
        $data    = db('advert') ->delete($id);
            
        if($data) {
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作完成'];
        }
    }
}
