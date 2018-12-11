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
use app\admin\model\InformationModel;

use think\Db;
class Information extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $search = input('searchText');
        $start =  strtotime(input('start_time'));
        $end = strtotime(input('end_time'));
		$filter = input('filter');
		
		$map['type'] = '图文咨询';
		
		$field = [
                'con.con_id', 'con.d_id', 'con.c_id', 'con.money', 'con.create_time',
                'con.state', 'con.comment_time', 'con.uc_id', 'con.Quick', 'con.appoint_time', 'con.total_time',
                'cu.nick_name as user_name','cu.email','do.avatar','do.nick_name as doctor_name',
        ];
		
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['do.nick_name'] = array('like', "%$search%");
            }
			
            if(!empty($start) && !empty($end)){
				$query_arr = $query_arr + ['start_time'=>empty($start)?"":date('Y-m-d H:i:s',$start),'end_time'=>empty($end)?"":date('Y-m-d H:i:s',$end)];
                $map['con.create_time'] = array('between', array($start, $end));
            }
			
			if($filter!=""){
				$query_arr = $query_arr + ['filter'=>$filter];
				
				$map['type'] = $filter;
				
				if($filter=="使用优惠券咨询"){
					$map['type'] = '图文咨询';
					$map['uc_id'] = ['neq','0'];
				}
			}
			
			
			if($filter=="视频咨询" or $filter=="电话咨询"){
				$field = [
                'con.con_id', 'con.d_id', 'con.c_id', 'con.money', 'con.create_time',
                'con.state', 'con.comment_time', 'appoint_time', 'total_time',
                'cu.nick_name as user_name','cu.email','do.avatar','do.nick_name as doctor_name',
        		];
			}
			
        }
		
		
        $map['service_id'] = '0';
		
		$count = db('consultation')
            ->alias('con')
            ->join('__DOCTOR__ do ', 'con.d_id= do.doctor_id', "LEFT")
            ->join('__USER__ cu ', 'con.c_id= cu.user_id', "LEFT")
            ->where($map)
            ->count();

		
        // 获得列表数据
        $list = new InformationModel();
		
		$list = $list->db('consultation')
                ->alias('con')
                ->field($field)
                ->join('__DOCTOR__ do ', 'con.d_id= do.doctor_id', "LEFT")
                ->join('__USER__ cu ', 'con.c_id= cu.user_id', "LEFT")
                ->where($map)
                ->order('con_id DESC')
                ->paginate(20,false,['query' => $query_arr]);
		
				
		foreach($list as $k => $v){
			if($filter=="图文咨询" or $filter=="" ){
				if($list[$k]['uc_id']==0){
					$list[$k]['uc_id'] = '<font color="#FF0000">否</font>';
				}else{
					$list[$k]['uc_id'] = '<font color="#337ab7">是</font>';
				}
				if($list[$k]['Quick']=='0'){
					$list[$k]['Quick'] = '<font color="#FF0000">否</font>';
				}else{
					$list[$k]['Quick'] = '<font color="#337ab7">是</font>';
				}
			}
			
			if($list[$k]['state']=='进行中'){
				$list[$k]['state'] = '<font color="#FF0000">进行中</font>';
			}else{
				$list[$k]['state'] = '<font color="#337ab7">已完成</font>';
			}
        }

		//打印语句
		//echo Db::getLastSql();
        return $this->fetch('information/index',['list' => $list,'count'=>$count,'filter'=>$filter,'search'=>$search,'start_time'=>empty($start)?"":date('Y-m-d H:i:s',$start),'end_time'=>empty($end)?"":date('Y-m-d H:i:s',$end)]);

    }




   
}
