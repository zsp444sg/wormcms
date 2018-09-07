<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class AuthRule extends Validate{
    protected $rule =   [
        'title'  => 'unique:auth_rule|require|min:3|max:80|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9-\_\s·]+$/u',
        'name'  => 'unique:auth_rule|require|max:150',
        'status'  => 'require|number|in:0,1',
        'open'  => 'require|number|in:0,1',
        'menusee'  => 'require|number|in:0,1',
        'pid'  => 'require|number',
        'sort'  => 'require|number',
        'condition'  => 'min:3',
    ];

    protected $message  =   [
        'title.require' => '权限名称不能为空！',
        'title.unique' => '权限名称不得重复！',
        'title.min' => '权限名称不得小于2字符！',
        'title.max' => '权限名称不得大于20字符！',
        'title.regex' => '权限名称不得使用特殊字符！',
        'name.require' => '规则地址不能为空！',
        'name.unique' => '规则地址不得重复！',
        'name.max' => '规则地址不得大于150字符！',
        'status.require' => '填写错误！',
        'status.number' => '填写错误！',
        'status.in' => '填写错误！',
        'open.require' => '填写错误！',
        'open.number' => '填写错误！',
        'open.in' => '填写错误！',
        'menusee.require' => '填写错误！',
        'menusee.number' => '填写错误！',
        'menusee.in' => '填写错误！',
        'pid.require' => '请选择上级分类！',
        'pid.number' => '请选择上级分类！',
        'pid.in' => '请选择上级分类！',
        'sort.require' => '请填写排序值！',
        'sort.number' => '排序值错误！',
    ];
    protected $scene = [
        // 'edit' => ['title'  => 'require','name'  => 'require']
    ];
}