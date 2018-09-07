<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class ArtModel extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'title'  => 'unique:art_model|require|chsDash|length:2,150',
        'futitle'  => 'unique:art_model|chsDash|length:2,150',
        'formtitle'  => 'chsAlphaNum|length:2,24',
        'formcontent'  => 'chsAlphaNum|length:2,24',
        'formedit_h'  => 'require|number',
        'formdescription'  => 'require|number',
        'formauthor'  => 'require|number',
        'formfor'  => 'require|number',
        'status'  => 'require|number|length:1',
        'sort'  => 'require|number',

        'sqlname'  => 'require|alphaDash',
        'sqltype'  => 'require|alpha',
        'formtype'  => 'require|alpha',
        'formvalue'  => 'chsDash',
        'formunist'  => 'chsAlphaNum',
        'formrequired'  => 'require|number',
        'seeauth'  => 'array',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'title.unique' => '模型名称已存在！',
        'title.require' => '模型名称不得为空！',
        'title.chsDash' => '模型名称只能是汉字、字母、数字和下划线_及破折号-！',
        'title.length' => '模型名称字数为4-50个文字！',
        'futitle.unique' => '模型别名已存在！',
        'futitle.chsDash' => '模型别名只能是汉字、字母、数字和下划线_及破折号-！',
        'futitle.length' => '模型别名字数为4-50个文字！',
        'formtitle.chsAlphaNum' => '表单名称只能是汉字、字母和数字！',
        'formtitle.length' => '表单名称字数为2-8个文字！',
        'formcontent.chsAlphaNum' => '表单名称只能是汉字、字母和数字！',
        'formcontent.length' => '表单名称字数为2-8个文字！',
        'formedit_h.require' => '编辑器高度不得为空！',
        'formcontent.number' => '编辑器高度只能为数字！',
        'formdescription.require' => '启用内容简介选择错误！',
        'formdescription.number' => '启用内容简介选择错误！',
        'formauthor.require' => '启用作者选择错误！',
        'formauthor.number' => '启用作者选择错误！',
        'formfor.require' => '启用来源选择错误！',
        'formfor.number' => '启用来源选择错误！',
        'status.require' => '是否启用选择错误！',
        'status.number' => '是否启用选择错误！',
        'sort.require' => '排序值不得为空！',
        'sort.number' => '排序值只能为数字！',
        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['id','title','futitle','formtitle','formcontent','formedit_h','formdescription','formauthor','formfor','status','sort','__token__'],
        'field' => ['id'],
    ];
}