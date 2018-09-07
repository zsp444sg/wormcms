<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Config extends Validate{
    protected $rule =   [
        'webname'  => 'require',
        'webkeywords'  => 'require',
        'description'  => 'require',
        'webmail'  => 'require|email',
        'web_open'  => 'require|number',
        'www_url'  => 'require',
        'temp'  => 'require|alphaDash',
        'user_temp'  => 'require|alphaDash',
        'updir'  => 'require|alphaDash',
        'web_beian'  => 'require|chsDash',
        'web_adminyz'  => 'require|number',
        'conf_title'  => 'require|chsAlphaNum',
        'conf'  => 'require|alphaDash',
        'conf_type'  => 'require|number',
        'nodel'  => 'require|number',
        'sort'  => 'require|number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'webname.require' => '网站名称不得为空！',
//        'webname.chsDash' => '网站名称只能是汉字、字母、数字和下划线_及破折号-！',
        'webkeywords.require' => '网站关键词不得为空！',
        'description.require' => '网站描述不得为空！',
        'webmail.require' => '管理员邮箱不得为空！',
        'webmail.email' => '管理员邮箱格式不正确！',
        'web_open.require' => '请选择网站是否开放！',
        'web_open.number' => '请选择网站是否开放！',
        'www_url.require' => '请输入网站域名！',
        'temp.require' => '请选择网站风格！',
        'temp.alphaDash' => '请选择网站风格！',
        'user_temp.require' => '请选择会员中心风格！',
        'user_temp.alphaDash' => '请选择会员中心风格！',
        'updir.require' => '请输入附件上传目录！',
        'updir.alphaDash' => '附件上传目录输入错误！',
        'web_beian.require' => '请输入网站备案号！',
        'web_beian.chsDash' => '请输入网站备案号！',
        'web_adminyz.require' => '请选择后台登录是否启用验证码！',
        'web_adminyz.number' => '请选择后台登录是否启用验证码！',
        'conf_title.require' => '配置名称不得为空！',
        'conf_title.chsAlphaNum' => '配置名称只能为中文、字母和数字！',
        'conf.chsAlphaNum' => '配置名称只能为中文、字母和数字！',
        'conf.unique' => '配置键名已存在！',
        'conf.require' => '配置键名不得为空！',
        'conf.alphaDash' => '配置键名只能为字母和数字，下划线_及破折号-！',
        'conf_type.require' => '配置类型选择错误！',
        'conf_type.number' => '配置类型选择错误！',
        'nodel.require' => '禁止删除选择错误！',
        'nodel.number' => '禁止删除选择错误！',
        'sort.require' => '排序值填写错误！',
        'sort.number' => '排序值填写错误！',
        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'index' => ['webname','webkeywords','description','webmail','web_open','www_url','temp','user_temp','updir','web_beian','web_adminyz','__token__'],
        'add' => ['conf_title','conf','conf_type','nodel','sort','__token__'],
    ];
}