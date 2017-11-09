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
        return $is_online[$value];
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
        return $category[$value];
    }
    //在线状态字段:is_online返回值处理
    public function getIs_onlineAttr($value)
    {
        $is_online = [
            0=>'未在线',
            1=>'已在线'
        ];
        return $is_online[$value];
    }
}