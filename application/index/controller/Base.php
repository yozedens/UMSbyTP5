<?php
namespace app\index\controller;
use think\Controller;
use think\Session;

class Base extends Controller 
{
    //初始化操作
    protected function _initialize()
    {
        parent::_initialize(); //调用父类中的初始化操作
        define('USER_NAME',Session::has('user_name')?Session::get('user_name'):null);
    }

    //判断用户在进入后台时是否已登录，没有登录则弹出提示并跳转到登录页面
    //放在后台的入口index/index操作中
    protected function isLogin()
    {
        if(is_null(USER_NAME))
        {
            $this->error('用户未登录，无权访问','user/login');
        }
    }

    //防止用户重新登录，放在user/login
    protected function alreadyLogin()
    {
        //其实以下逻辑是只能允许一个用户登录，因为没有判断USER_NAME的具体值，即没有区分用户
        if(USER_NAME)
        {
            $this->error('已有用户'.USER_NAME.'登录，请勿重新登录','index/index');
        }
    }
}