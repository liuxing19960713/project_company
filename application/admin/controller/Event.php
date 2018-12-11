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
use app\common\tools\Easemob;
use think\Request;
use app\admin\model\EventModel;
use app\admin\model\DoctorModel;

use think\Db;
class Event extends Base
{
    // 文章列表
    public function index(Request $request = null)
    {

		
		$map = [];
        $query_arr = [];
        $map['is_deleted'] = 'no';
        $search = input('searchText');
        if ($_GET) {
            if (!empty($search)) {
                $query_arr = ['searchText'=>$search];
                $map['title'] = array('like', "%$search%");
            }
        }
		
		
        $count = EventModel::whereCount($map);
        // 获得列表数据
        $list = new EventModel();
		$list = $list->where($map)->order('event_id desc')->paginate(10,false, ['query' => $query_arr]);
		
        //$this->assign('count', $count);
		//$this->assign('list', $list);
		
		foreach($list as $k => $v){
			$list[$k]['operate'] = showOperate($this->makeButton($v['event_id']));
			
			if($list[$k]['doctor_avatar']==""){
				$list[$k]['doctor_avatar'] = "http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png";
			}
        }
			
		//打印语句
		//echo Db::table('yyb_admin')->getLastSql();
        return $this->fetch('event/index',['list' => $list,'count'=>$count]);

    }

    // 添加文章
    public function Add()
    {
        if(request()->isPost()){
            $param = input('post.');
			
			$doctorInfo = DoctorModel::get($param['doctor_id']);

			if (empty($doctorInfo)) {
				$this->setRenderMessage('医生ID不存在');
				return $this->getRenderJson();
			}
			
			$param['doctor_name'] = $doctorInfo['nick_name'];
			$param['doctor_title'] = $doctorInfo['title'];
			$param['doctor_avatar'] = $doctorInfo['avatar'];
			$param['doctor_info'] = $doctorInfo['intro1'];
			$param['start_time'] =  strtotime($param['start_time']);
			$param['end_time'] = strtotime($param['end_time']);
			$param['create_time'] = time();

            $article = new EventModel();
            //$flag = $article->add($param);
        	$flag = EventModel::create($param);
			
			if (empty($flag)) {
				$this->setRenderMessage('数据错误不存在');
				return $this->getRenderJson();
			}
			$easeMob = new Easemob();
			$groupId = $easeMob->createGroup(config('easemob'), $flag['event_id']);
			$updateData = array(
					'group_id' => $groupId,
			);
			$goodsResult = db('event')
                        ->where('event_id', $flag['event_id'])
                        ->update($updateData);
			
			return $this->fetch();	
            //return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }
	
	

    public function Edit()
    {
        $article = new EventModel();
        if(request()->isPost()){

            $param = input('post.');
			
			$doctorInfo = DoctorModel::get($param['doctor_id']);

			if (empty($doctorInfo)) {
				$this->setRenderMessage('医生ID不存在');
				return $this->getRenderJson();
			}
			
			$param['doctor_name'] = $doctorInfo['nick_name'];
			$param['doctor_title'] = $doctorInfo['title'];
			$param['doctor_avatar'] = $doctorInfo['avatar'];
			$param['doctor_info'] = $doctorInfo['intro1'];
			
			$param['start_time'] =  strtotime($param['start_time']);
			$param['end_time'] = strtotime($param['end_time']);
			
            $flag = $article->edit($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'show' => $article->getOneEvent($id)
        ]);
        return $this->fetch();
    }

    public function Del()
    {
        $id = input('param.id');

        $article = new EventModel();
        $flag = $article->delEvent($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    // 上传缩略图
    public function uploadImg()
    {
        if(request()->isAjax()){

            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info){
                $src =  '/upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            }else{
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
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
                'auth' => 'event/edit',
                'href' => url('event/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'event/del',
                'href' => "javascript:Del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
