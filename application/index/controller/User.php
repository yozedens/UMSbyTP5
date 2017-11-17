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
        //该逻辑是只能允许一个用户登录，
        $this->alreadyLogin(); //判断是否已经有用户登录
        //若需改变逻辑为不允许同一用户重复登录而允许不同用户同时登录，可开启checkLogin方法中的相关代码并屏蔽上一语句
        
        $this -> view -> assign('title', '系统登录');
        $this -> view -> assign('keywords', '用户管理系统登录');
        $this -> view -> assign('desc', '用户管理系统登录');
        $this -> view -> assign('copyright', '2017 北京东方晨旭科技发展有限公司 Copyright by esunrising');

        //渲染模板，默认为：当前模块/默认视图目录/当前控制器（小写）/当前操作（小写）.html
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
                //下一次修改增加判断在线状态的逻辑

                $user_info = $user->getData(); //用户所有信息
                //若需改变逻辑为不允许同一用户重复登录而允许不同用户同时登录，可开启下一代码块并屏蔽login方法中相关操作
                if($user_info['is_online']){
                    $result = '该用户已在别处登录或上次登录未正常注销，请重试或联系管理员';
                    return ['status'=>$status,'message'=>$result,'data'=>$data];
                }
                $status = 1;
                $result = '验证通过，点击[确定]进入';
                //改变在线状态
                UserModel::update(['is_online'=>1],['name'=>$user->name]);
                //用session设置用户登录信息
                Session::set('user_name',$user->name);  //用户的name存入session
                Session::set('user_info',$user_info);  //用户的所有信息存入session
                //获取用户类别，此处直接处理，下一次修改采用数据库存储及查询
                $category = $user_info['category'];
                $user_category = '无法识别用户类别';
                switch ($category) {
                    case 0:
                        $user_category = "超级管理员";
                        break;
                    case 1:
                        $user_category = "管理员";
                        break;
                    case 10:
                        $user_category = "重要用户";
                        break;
                    case 11:
                        $user_category = "普通用户";
                        break;
                    case 12:
                        $user_category = "标黑用户";
                        break;
                }
                Session::set('user_category',$user_category);
            }

        }
        //这个'data'=>$data返回值好像没有用到？！！
        return ['status'=>$status,'message'=>$result,'data'=>$data];
    }
    //退出登录
    public function logout()
    {
        //改变在线状态
        UserModel::update(['is_online'=>0],['name'=>Session::get('user_name')]);
        //注销Session
        Session::delete('user_name');
        Session::delete('user_info');
        $this->success('注销登录，正在返回','user/login'); //输出提示信息，并跳转到制定地址
    }

    //管理员列表
    public function adminlist()
    {
        //"分配变量值"
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords', '用户管理系统');
        $this -> view -> assign('desc', '用户管理系统管理员列表页面');

        //查询数据条数    //为什么不用assign()方法？？？
        $this -> view -> count = UserModel::count();

        //权限区分，根据权限不同获取不同量的数据
        $category = Session::get('user_info.category');
        if($category == 0||$category == 1){  //超级管理员或管理员，可以查看所有记录
            $list = UserModel::all();  //数据通过模型获取器处理,think\Model.php中的静态方法
        }else{  //非管理员只能查看自己一条的数据
            //此时其实可以用think\Model::get()方法，但想要共用list，则同样用all()
            $list = UserModel::all(['name'=>Session::get('user_name')]);
        }
        //"分配变量值"
        $this->view->assign('list',$list);
        //渲染管理员列表模板
        return $this->view->fetch('admin_list');
    }

    //管理员状态变更
    public function setIsOnline(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        if($result->getData('is_online') == 1) {
            UserModel::update(['is_online'=>0],['id'=>$user_id]);
        } else {
            UserModel::update(['is_online'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $user_id = $request -> param('id');  //这里的request获取的请求数据就是$.get请求中的数据
        $result = UserModel::get($user_id);
        $this->view->assign('title','编辑用户信息');
        $this->view->assign('keywords','esunrising.com');
        $this->view->assign('desc','编辑用户信息');
        $this->view->assign('user_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //修改用户信息
    public function editUser(Request $request)
    {
        //获取表单数据
        $param = $request -> param();
        //什么情况下提交的表单项为空？
        foreach ($param as $key => $value) {
            if (!empty($value)) {   //判断该项的值不为空
                $data[$key] = $value;
            }
        }

        $condition = ['id'=>$data['id']]; //id由隐藏表单项提交
        $result = UserModel::update($data,$condition); //修改对应id用户的数据

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        UserModel::destroy($user_id);

    }
     //添加操作的界面
     public function  adminAdd()
     {
         $this->view->assign('title','添加用户');
         $this->view->assign('keywords',' ');
         $this->view->assign('desc',' ');
         return $this->view->fetch('admin_add');
     }
     //添加用户操作
     public function addUser(Request $request)
     {
        $data = $request->param();
        $status = 0;
        $result = '';

        $rule = [
            'name|用户名' => 'require|regex:/^[a-zA-Z_][\w_]{2,15}$/',
            //密码项的正则表达式的效果是清楚的，但不是很懂?!的使用
            'pwd|密码' => 'require|regex:/^(?![0-9]+$)(?![a-zA-Z]+$)\S{6,16}$/',
            'idcard|身份证号' =>['require','regex'=>'/^(\d{18}|\d{17}X)$/'],
            'email|邮箱' =>'require|email'
        ]; //下次修改可加入正则表达式的验证条件

        //自定义验证失败时的提示信息
        $msg =[
            'name'=>[
                'require'=>'用户名不能为空，请检查',
                'regex'=>'用户名格式错误！3-16位字母数字下划线组合，且不能以数字开头'
                ], //键为验证规则，值为对应的规则验证失败时的提示信息
            'pwd'=>[
                'require'=>'密码不能为空，请检查',
                'regex'=>'密码格式错误！6-16位字母数字半角特殊字符组合，且必须包含字母和数字'
                ],
            'idcard'=>[
                'require'=>'身份证号不能为空，请检查',
                'regex'=>'身份证号格式错误！18位数字或17位数字后跟X'
                ],
            'email'=>[
                'require'=>'邮箱不能为空，请检查',
                'email'=>'邮箱格式错误，请检查']
        ];

        $result = $this -> validate($data, $rule, $msg);

        if($result === true){
            //创建数据，其中密码会自动调用修改器加密
            $user = UserModel::create($request->param());
            if($user === null){
                $result = '添加失败';
            }else{
                $status = 1;
                $result = '添加成功';
            }
        }

        return ['status'=>$status, 'message'=>$result];
     }

     public function checkUserName(Request $request)
     {
        $username = trim($request->param('name'));
        $status = 1;
        $message = '';

        if(UserModel::get(['name'=>$username])){
            $status = 0;
            $message = '用户名已被使用，请重新输入';
        }

        return ['status'=>$status,'message'=>$message];
     }


     public function checkUserEmail(Request $request)
     {
        $email = trim($request->param('email'));
        $status = 1;
        $message = '';

        if(UserModel::get(['email'=>$email])){
            $status = 0;
            $message = '邮箱已被使用，请重新输入';
        }

        return ['status'=>$status,'message'=>$message];
     }
}
