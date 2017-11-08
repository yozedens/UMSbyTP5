<?php
namespace app\index\controller;

use app\index\controller\Base;

class Index extends Base
{
    //用户管理系统后台入口方法
    public function index()
    {
        $this->isLogin(); //判断用户是否登录，若没有登录则会跳转到登录页面
        return $this->view->fetch();
    }
}
