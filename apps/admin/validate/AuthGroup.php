<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class AuthGroup extends Validate{
    protected $rule =   [
        'title'  => 'unique:auth_group|require|min:3|max:80|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9-\_\s·]+$/u',
        'status'  => 'require|number|in:0,1',
        'grouptype'  => 'require|number|in:0,1',
        'groupadmin'  => 'require|number|in:0,1',
        'groupup'  => 'require|number',

        'id' => 'require|number',
    ];

    protected $message  =   [
        'title.require' => '用户组名称不能为空！',
        'title.unique' => '用户组名称不得重复！',
        'title.min' => '用户组名称不得小于2字符！',
        'title.max' => '用户组名称不得大于20字符！',
        'title.regex' => '用户组名称不得使用特殊字符！',
        'status.require' => '填写错误！',
        'status.number' => '填写错误！',
        'status.in' => '填写错误！',
        'grouptype.require' => '请选择用户组类型！',
        'grouptype.number' => '请选择用户组类型！',
        'grouptype.in' => '请选择用户组类型！',
        'groupadmin.require' => '请选择是否允许后台登录！',
        'groupadmin.number' => '请选择是否允许后台登录！',
        'groupadmin.in' => '请选择是否允许后台登录！',
        'groupup.require' => '请填写用户升级积分！',
        'groupup.number' => '请填写用户升级积分！',



    ];
    protected $scene = [
        'add' => ['title','status','grouptype','groupadmin','groupup'],
        'auhtgroupauth' => ['id'],
    ];
}