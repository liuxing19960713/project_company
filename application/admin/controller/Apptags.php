<?php
//APP搜索关键词管理控制器
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\model\ApptagsModel;
class Apptags extends Base {
    //初始页，显示所有数据
    public function index(){
        $member = new ApptagsModel();
        $data = $member -> getAll();
        $title = "APP搜索关键词列表";
        //绑定数据
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('apptags/index');//,['title' => $title,'data' => $data]);
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
        $title = "APP搜索关键词列表";
        //绑定模板变量
        $this -> assign('title',$title);
        $this -> assign('data',$data);
        return $this -> fetch('apptags/index');//,['title' => $title,'data' => $data]);
    }

    //添加关键词
    public function adddata() {
        //获取前端数据并对数据进行处理
        //$post = $_POST ? $_POST : '';
        $member = new ApptagsModel();
        //检测数据库是否已经存在该数据字段(多条件)
        $where = [];
        $where['tagname'] = $_POST['AddName'];
        $where['type'] =  $_POST['AddType'];
        $return = $member -> getSelect($where);
        if($return) {
            echo '这个关键词已经存在';
        } else {
            $where['pro'] = $_POST['AddPrty'];
            $where['hide'] = $_POST['AddHide'];
            $where['addtime'] = date('Y-m-d h:i:s', time());
            //数据放进数据库
            $member -> AddData($where);
            echo '新建关键词成功！';
        }
    }

    //删除关键词
    public function DeleteData() {
        $member = new ApptagsModel();
        //单条件
        $where = [];
        $where['id'] = $_POST['id'];
        $data = $member -> getSelect($where);
        $member -> DeleteData($where);
        print_r("关键字：".$data[0]['tagname'] . " 删除成功");
        
    }

    //编辑\修改关键词
    public function SearchData() {
        $member = new ApptagsModel();
        //检测数据库是否已经存在该数据字段(多条件)
        $where = [];
        $where['tagname'] = $_POST['tagname'];
        $where['type'] =  $_POST['type'];
        $where['hide'] = $_POST['hide'];
        $where['pro'] = $_POST['pro'];
        $return = $member -> getSelect($where);
        if($return) {
            echo '这个关键词已经存在';
        } else {
            $where2 = [
                'id' => $_POST['id']
            ];
            $UpData = [];
            $UpData['tagname'] = $_POST['tagname'];
            $UpData['pro'] = $_POST['pro'];
            $UpData['hide'] = $_POST['hide'];
            $UpData['type'] = $_POST['type'];
            $member -> AlterData($where2,$UpData);
            print_r("关键字修改成功");
        }
    }

    //模糊搜索
    public function Search() {
        $member = new ApptagsModel();
        $data2 = $_GET['tagname'];
        $where = [];
        //设置模糊搜索条件
        $where['tagname'] = array('like','%'.$data2.'%');
        $return = $member -> getSelect($where);
        $title = "APP搜索关键词列表";
        //绑定数据
        $this -> assign('title',$title);
        $this -> assign('data',$return);
        return $this -> fetch('apptags/index');//,['title' => $title,'data' => $data]);
    }
}
?>