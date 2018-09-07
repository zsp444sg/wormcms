<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\install\validate;
use think\Validate;
class Install extends Validate{
    protected $rule =   [
        'hostname'  => 'require|regex:/^[a-zA-Z0-9_.@~!?]{5,20}$/',
        'database'  => 'require|regex:/^[a-zA-Z0-9_.@~!?]{4,50}$/',
        'username'  => 'require|regex:/^[a-zA-Z0-9_-]{4,30}$/',
        'password'  => 'require|regex:/^[a-zA-Z0-9_.@~!?]{4,30}$/',
        'hostport'  => 'require|number',
        'prefix'  => 'require|regex:^[a-z0-9]{1,20}[_]{1}',

        'webname'  => 'require',
        'userpassword'  => 'require|regex:/^[a-zA-Z0-9_.@~!?]{6,30}$/',
        'ruserpassword'  => 'require|confirm:userpassword|regex:/^[a-zA-Z0-9_.@~!?]{6,30}$/',
        'shibiefu'  => 'require|different:userpassword|regex:/^[a-zA-Z0-9_.@~!?]{6,30}$/',

        '__token__' => 'token',
    ];

    protected $message  =   [
        'hostname.require' => '数据库地址不得为空！',
        'hostname.regex' => '数据库地址不正确！',

        'database.require' => '数据库名不得为空！',
        'database.regex' => '数据库名不正确！',

        'username.require' => '用户名不得为空！',
        'username.regex' => '用户名应为5-30个字符，不得包含特殊字符！',

        'password.require' => '数据库密码不得为空！',
        'password.regex' => '数据库密码错误！',

        'hostport.require' => '数据库端口不得为空！',
        'hostport.number' => '数据库端口格式不正确！',

        'prefix.require' => '数据表前缀不得为空！',
        'prefix.regex' => '数据表前缀不正确！',

        'webname.require' => '网站标题不得为空！',

        'userpassword.require' => '用户密码不得为空！',
        'userpassword.regex' => '用户密码为6-30位字母数字组合，不得包含特殊字符！',

        'ruserpassword.require' => '重复密码不得为空！',
        'ruserpassword.confirm' => '重复密码不正确！',
        'ruserpassword.regex' => '重复密码不正确！',

        'shibiefu.require' => '超级识别符不得为空！',
        'shibiefu.different' => '超级识别符不得和密码一致！',
        'shibiefu.regex' => '超级识别符为6-30位字母数字组合，不得包含特殊字符！',

        '__token__' => '数据令牌已过期，请刷新页面！',
    ];
    protected $scene = [
        'sqldata' => ['hostname','database','username','password','hostport','prefix'],
        'webdata' => ['webname','username','userpassword','ruserpassword','shibiefu'],

    ];
}