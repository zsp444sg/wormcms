<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class User extends Validate{
    protected $rule =   [
        'group_id'  => 'require|number',
        'status'  => 'require|number|length:1',
        'uid'  => 'require|number',

        'username'  => 'unique:user_data,uphone|unique:user|require|max:20|min:6|regex:/^[a-zA-Z0-9_.@~!?]{5,20}$/',
        'uniname'  => 'unique:user_data|require|max:20|min:5',
        'uemail'  => 'unique:user_data,username|require|email',

        'upassword'  => 'require|max:20|min:6|regex:/^[a-zA-Z0-9_.@~!?]{6,20}$/',
        'rpassword'  => 'require|confirm:upassword',

        'uname'  => 'max:16|min:4',
        'usex'  => 'number|length:1',
        'uphone'  => 'unique:user_data,username|number|regex:/^1[34578]\d{9}$/',
        'uicq'  => 'number',
        'ubday'  => 'dateFormat:Y-m-d',
        'ucard'  => 'length:14,20',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'group_id.require' => '请选择用户组！',
        'group_id.number' => '请选择用户组！',
        'status.require' => '请选择是否通过！',
        'status.number' => '请选择是否通过！',
        'status.length' => '请选择是否通过！',
        'uid.require' => '表单提交错误！',
        'uid.number' => '表单提交错误！',

        'username.require' => '用户名不能为空！',
        'username.unique' => '用户名已存在！',
        'username.max' => '用户名不得超过20位！',
        'username.min' => '用户名不得小于6位！',
        'username.regex' => '用户名只能是由字母、数字及英文符号组合！',
        'uniname.unique' => '用户昵称已存在！',
        'uniname.require' => '用户昵称不得为空！',
        'uniname.max' => '用户昵称不得超过10个字符！',
        'uniname.min' => '用户昵称不得小于3个字符！',
        'uemail.unique' => '电子邮箱已存在！',
        'uemail.require' => '电子邮箱不得为空！',
        'uemail.email' => '电子邮箱填写错误！',

        'upassword.require' => '密码不能为空！',
        'upassword.max' => '密码不得超过25位！',
        'upassword.min' => '密码不得小于6位！',
        'upassword.regex' => '密码只能是英文字母、数字或下划线！',
        'rpassword.require' => '重复密码不得为空！',
        'rpassword.confirm' => '两次密码不一致！',

        'uname.max' => '真实姓名长度不得超过8个汉字！',
        'uname.min' => '真实姓名长度不得小于2汉字！',
        'usex.number' => '性别选择错误！',
        'usex.length' => '性别选择错误！',
        'uphone.number' => '手机号码填写错误！',
        'uphone.regex' => '手机号码填写错误！',
        'uphone.unique' => '手机号码已存在！',
        'uicq.number' => 'QQ填写错误！',
        'uicq.unique' => 'QQ已存在！',
        'ubday.dateFormat' => '出生日期填写错误！',
        'ucard.length' => '身份证填写错误！',

    ];
    protected $scene = [
        'add' => ['username','uniname','uemail','upassword','group_id','status','uname','usex','uphone','uicq','ubday','ucard'],
        'edit' => ['uid','uniname','uemail','upassword'=>'max:20|min:6|regex:/^[a-zA-Z0-9_.@~!?]{6,20}$/','group_id','status','uname','usex','uphone','uicq','ubday','ucard'],
    ];
}