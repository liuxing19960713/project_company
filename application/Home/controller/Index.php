<?php
namespace app\Home\controller;
use think\Controller;
use think\Db;
class Index extends Controller
{
    public function index()
    {
        return $this -> fetch('index/index');
        // return $this->fetch('Index/index');
    }
}
