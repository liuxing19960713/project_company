<?php
//轮播图管理
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\Admin\model\CarouselModel;
class Carousel extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $count = count($info = db("carousel")->select());
        $title = "轮播图列表";
        return $this->fetch("Carousel/index",['title'=>$title,"count"=>$count,"info"=>$info]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        
        return $this->fetch("Carousel/create",['title'=>'轮播图添加']);
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
        $res    = $request->param();
        $res['addtime'] = date("Y-m-d H:i:s",time());

        $data   = new CarouselModel($res);
        // 过滤post数组中的非数据表字段数据
        $result         = $data->allowField(true)->save();
        if ($result) {
            return 'ok';
        }else{
            return 'error';
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
        //
        $DoctorData = db("carousel")->where("id",$id)->find();
         $title      = "轮播图的编辑";
          //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        // var_dump($detail);
        return $this->fetch("carousel/alert",['DoctorData'=>$DoctorData,'QnToken'=>$QnToken,'title'=>$title]);
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
        //
        $info = db("carousel")->where("id",$id)->find();
        // var_dump($info);
        $res  = $request->except(['/admin/carousel/update','id']);
        if ($res['img_url'] == "") {
            $res['img_url'] = $info['img_url'];
        }
        $result = new CarouselModel();
        // 过滤post数组中的非数据表字段数据
        $result->allowField(true)->save($res,['id' => $id]);
        if ($result) {
            return 'ok';
        }else{
            return 'error';
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
        // $res = CarouselModel::destroy($id);
        $res = CarouselModel::where('id',$id)->delete();
        if ($res) {
           return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作失败'];
        }
    }
}
