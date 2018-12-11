<?php
//APP搜索关键词管理控制器
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\model\ConxuanxModel;
class Conxuanx extends Base {
    //初始页，显示所有数据
    public function index(){
        $member = new ConxuanxModel();
        $data = $member -> getAll();
		$sort = db('level') -> select();
		//echo Db::getLastSql();
        //绑定数据
        $this -> assign('sort',$sort);
        $this -> assign('data',$data);
        return $this -> fetch('conxuanx/index');//,['title' => $title,'data' => $data]);
    }

    //筛选获取不同类型的关键词
    /**
     * 关键词类型说明：
     * 0结果\1预加载\2医生搜索结果\3医生搜索预加载
     */
    public function ScreenData() {
        $Screenid = $_GET['Screenid'];
        $member = new ApptagsModel();
        //查询指定字段条件的数据
        $where = ['type' => $Screenid];
        $data = $member -> getSelect($where);
        //绑定模板变量
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('conxuanx/index');//,['title' => $title,'data' => $data]);
    }

    //添加关键词
    public function adddata() {
        //获取前端数据并对数据进行处理
        //$post = $_POST ? $_POST : '';
        $member = new ConxuanxModel();
        //检测数据库是否已经存在该数据字段(多条件)
        $where = [];
        $where['title'] = $_POST['title'];
        $where['sort'] =  $_POST['sort'];
        $return = $member -> getSelect($where);
        //数据放进数据库
        $member -> AddData($where);
        echo '新建成功！';
            
    }

    //删除关键词
    public function DeleteData() {
        $member = new ConxuanxModel();
        //单条件
        $where = [];
        $where['id'] = $_POST['id'];
        $data = $member -> getSelect($where);
        $member -> DeleteData($where);
        print_r("删除成功");
        
    }

    //编辑\修改关键词
    public function SearchData() {
        $member = new ConxuanxModel();
		
		$where = [
			'id' => $_POST['id']
		];
		
		$UpData = [];
        $UpData['title'] = $_POST['title'];
        $UpData['sort'] =  $_POST['hide'];
		
		$member -> AlterData($where,$UpData);
		print_r("关键字修改成功");

    }

    //模糊搜索
    public function Search() {
        $member = new ConxuanxModel();
        $data2 = $_GET['title'];
        $where = [];
        //设置模糊搜索条件
        $where['title'] = array('like','%'.$data2.'%');
        $return = $member -> getSelect($where);
		$sort = db('level') -> select();
		//echo Db::getLastSql();
        //绑定数据
        $this -> assign('sort',$sort);
        $this -> assign('data',$return);
        return $this -> fetch('conxuanx/index');//,['title' => $title,'data' => $data]);
    }
}
?>