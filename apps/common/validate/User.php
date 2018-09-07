<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-02-23
 * Time: 9:51
 */
namespace app\Common\validate;
use think\Validate;
class User extends Validate{
    protected $rule =   [
        'username'  => 'require|regex:/^1[34578]{1}[0-9]{9}$/',
        'upassword'  => 'require|length:6,20|regex:/^[a-zA-Z0-9_.@~!?]{6,20}$/',

        'uphone'  => 'unique:user,username|unique:user_data,uphone|require|regex:/^1[34578]{1}[0-9]{9}$/',
        'uname'  => 'require|chs',
        'uncode'  => 'require|number',
        'rupassword'  => 'require|confirm:upassword',

        'tuijian'  => 'regex:/^1[34578]{1}[0-9]{9}$/',
        
        'regxy'  => 'require|eq:1',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'uphone.unique' => '手机号码已存在！',
        'uphone.require' => '手机不得为空！',
        'uphone.regex' => '手机填写错误！',

        'uname.require' => '姓名不得为空！',
        'uname.chs' => '姓名只能为汉字！',
        'upassword.require' => '密码不得为空！',
        'upassword.length' => '密码请控制在6-20位！',
        'upassword.regex' => '密码只能是英文字母、数字或下划线！',

        'username.require' => '用户名不得为空！',
        'username.regex' => '用户名填写错误！',

        'uncode.require' => '验证码不得为空！',
        'uncode.number' => '验证码填写错误！',

        'rupassword.require' => '重复密码不得为空！',
        'rupassword.confirm' => '两次密码不一致！',

        'regxy.require' => '请同意注册协议！',
        'regxy.eq' => '请同意注册协议！',
    ];
    protected $scene = [
        'reg' => ['uphone','uname','upassword','rupassword','__token__'],
        'login' => ['username','upassword'],
        'repwd' => ['uphone'=>'require|regex:/^1[34578]{1}[0-9]{9}$/','uncode','upassword','rupassword'],
    ];
}