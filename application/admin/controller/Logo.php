<?php
//Logo管理
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Logo extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //显示所有的logo
        $count = count($info = DB::table("yyb_logo")->paginate(5));
        $title = "logo列表";
        return $this->fetch("Logo/index",["info"=>$info,"count"=>$count,"title"=>$title]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        $title = '添加logo';
        return $this->fetch("Logo/create",['title'=>$title]);
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
        $data = $request->param();
        $data['addtime'] = date("Y-m-d H:i:s",time());

        if (db('logo')->insert($data)) {
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
        $DoctorData = db("logo") ->where("id" ,$id) ->find();
        $title      = "logo的编辑";
          //创建七牛云Token
        $AccessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        $SecretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        $Bucket = "video1";
        $Domain = "cdn.uyihui.cn";
        $QnToken = $this -> QnToken("HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn","2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4","video1");
        // var_dump($detail);
        return $this->fetch("Logo/Alert",['DoctorData'=>$DoctorData,'QnToken'=>$QnToken,'title'=>$title]);
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
        $res  =  $request->except(['/admin/Logo/update','id']);
        $info = db("logo")->where("id",$id)->find();
        if ($res['img_url'] == "") {
            $res['img_url'] = $info['img_url'];
        }
        if (db("logo")->where("id",$id)->update($res)) {
            return 'ok';
        }else{
            return 'error';
        }
        var_dump($res);
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
        $data    = db('logo')->delete($id);
        
        if($data) {
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作完成'];
        }

    }
}
