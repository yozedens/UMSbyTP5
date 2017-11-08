<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Request;
use app\index\model\User as UserModel;
use think\Session;

class User extends Base
{
    //网页登录
    public function login()
    {
        $this->alreadyLogin(); //判断是否已经有用户登录
        return $this->view->fetch();
    }
    //登录验证,$this->validate($data,$rule,$msg);
    //参数分别为：需要验证的数据，验证规则，验证失败后返回的提示信息
    public function checkLogin(Request $request)
    {
        //初始化返回参数
        $status = '0'; //当前请求的状态，0代表请求失败
        $result = ''; //返回的错误信息
        $data = $request->param();
        
        //创建验证规则
        $rule = [
            'username|用户名' => 'require', //用户名必填
            'pwd|密码' => 'require', //密码必填
            'verify|验证码' => 'require|captcha', //验证码必填
        ];
        
        //自定义验证失败时的提示信息
        $msg =[
            'username'=>['require'=>'用户名不能为空，请检查'], //键为验证规则，值为对应的规则验证失败时的提示信息
            'pwd'=>['require'=>'密码不能为空，请检查'],
            'verify'=>[
                'require'=>'验证码不能为空，请检查',
                'captcha'=>'验证码输入错误，请检查']
        ];

        //进行验证,此处必须全等===才可以,因为验证不通过,$result保存错误信息字符串,返回非零
        $result = $this->validate($data,$rule,$msg);
        //result只会返回两种状态，true表示验证通过，字符串则表示用户定义的验证失败时的提示信息


        //如果验证成功，进行数据库操作
        if($result === true){
            //创建查询条件
            $map =[
                'name'=>$data["username"],
                'pwd'=>md5($data["pwd"])
            ];
            //数据表查询,返回模型对象
            $user = UserModel::get($map);  //返回用户信息对象，包含查询到的用户数据各字段数据
            if($user == null){
                $result = '没有找到该用户或用户密码错误';
            }else{
                $status = 1;
                $result = '验证通过，点击[确定]进入';
                //用session设置用户登录信息
                Session::set('user_name',$user->name);  //用户的id
                Session::set('user_info',$user->getData());  //用户的所有信息
            }

        }

        return ['status'=>$status,'message'=>$result,'data'=>$data];
    }
    //退出登录
    public function logout()
    {
        //注销Session
        Session::delete('user_name');
        Session::delete('user_info');
        $this->success('注销登录，正在返回','user/login'); //输出提示信息，并跳转到制定地址
    }
}
