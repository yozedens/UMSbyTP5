<?php
namespace app\index\controller;

use app\index\controller\Base;

class Index extends Base
{
    //用户管理系统后台入口方法
    public function index()
    {
        $this->isLogin(); //判断用户是否登录，若没有登录则会跳转到登录页面
        $this->view->assign('title','用户管理系统');
        $this -> view -> assign('keywords', '用户管理系统');
        $this -> view -> assign('desc', '用户管理系统');
        $this -> view -> assign('copyright', '2017 北京东方晨旭科技发展有限公司 Copyright by esunrising');

        return $this->view->fetch();
    }
}
