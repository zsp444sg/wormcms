<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class UserData extends Validate{
    protected $rule =   [
        'title'  => 'require|chsDash',
        'sqlname'  => 'require|alphaDash',
        'sqltype'  => 'require|alpha',
        'formtype'  => 'require|alpha',
        'formvalue'  => 'chsDash',
        'formunist'  => 'chsAlphaNum',
        'formrequired'  => 'require|number',
        'seeauth'  => 'array',
        'sort'  => 'require|number',
    ];

    protected $message  =   [
        'title.require' => '字段名不得为空！',
        'title.chsDash' => '字段名只能是汉字、字母、数字和下划线_及破折号-！',
        'sqlname.require' => '数据库键名不得为空！',
        'sqlname.alphaDash' => '数据库键名只能是为字母和数字，下划线_！',
        'sqltype.require' => '数据类型选择错误！',
        'sqltype.alpha' => '数据类型选择错误！',
        'formtype.require' => '表单类型选择错误！',
        'formtype.alpha' => '表单类型选择错误！',
        'formvalue.chsDash' => '表单默认值只能是汉字、字母、数字和下划线_及破折号-！',
        'formunist.chsAlphaNum' => '字段单位只能是汉字、字母和数字！',
        'formrequired.require' => '请选择是否必填！',
        'formrequired.number' => '请选择是否必填！',
        'seeauth.array' => '请重新选择允许查看的用户组！',
        'sort.require' => '排序值不得为空！',
        'sort.number' => '排序值只能为0以上数字！',


    ];
    protected $scene = [
        'add' => ['group_id','username','uemail','upassword','rpassword','status'],
        'edit' => ['group_id','uemail'=>'require|email','uniname'=>'require|max:20|min:5','uname'=>'max:20|min:6|chs','usex','uicq'=>'number','ubday','uphone'=>'number','upassword'=>'max:20|min:6|regex','status'],
        'login' => ['username'=>'require|max:20|min:5|alphaDash','password'=>'require|max:20|min:6|alphaDash'],
        'adduserdata' => ['title','sqlname','sqltype','formtype','formvalue','formunist','formrequired','seeauth','sort'],

    ];
}