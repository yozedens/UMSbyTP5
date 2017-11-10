<?php
namespace app\index\model;
use think\Model;

class User extends Model
{
    protected $table = 'tp5_user'; 


    //在线状态字段:is_online返回值处理
    public function getIsOnlineAttr($value)
    {
        $is_online = [
            0=>'未在线',
            1=>'已在线'
        ];
        if(isset($is_online[$value])){
            return $is_online[$value];
        }else{
            return "--";
        }
    }

    //数据表中用户类别字段:category返回值处理
    public function getCategoryAttr($value)
    {
        $category = [
           0=>'超级管理员',
            1=>'管理员',
            10=>'重要用户',
            11=>'普通用户',
            12=>'标黑用户'
        ];
        if(isset($category[$value])){
            return $category[$value];
        }else{
            return "--";
        }
    }
    //性别字段:sex返回值处理
    public function getSexAttr($value)
    {
        $sex = [
            'm'=>'男',
            'f'=>'女',
        ];
        if(isset($sex[$value])){
            return $sex[$value];
        }else{
            return "--";
        }
    }

    //密码修改器
    public function setPwdAttr($value)
    {
        return md5($value);
    }
}