<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Part extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'mid'  => 'require|number',
        'pid'  => 'require|number',
        'class'  => 'require|number|length:1',
        'title'  => 'unique:part|max:150|min:2',

        'maxpage' => 'number',
        'maxnum' => 'number',
        'listorder' => 'require|number',
        'plate' => 'require|alphaDash',
        'comment' => 'number',
        'pidsee' => 'number',

        'password' => 'alphaDash',
        'num' => 'number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误，请刷新页面！',
        'id.number' => '表单填写错误，请刷新页面！',
        'mid.require' => '请选择所属模型！',
        'mid.number' => '请选择所属模型！',
        'pid.require' => '请选择上级分类！',
        'pid.number' => '请选择上级分类！',
        'class.require' => '请选择栏目类型！',
        'class.number' => '请选择栏目类型！',
        'class.length' => '请选择栏目类型！',
        'title.unique' => '栏目已存在，请重新填写！',
        'title.max' => '栏目名称为2到50个字符！',
        'title.min' => '栏目名称为2到50个字符！',
        'title.regex' => '栏目名称不得存在特殊字符！',

        'maxpage.number' => '显示内容数量填写错误，只能为数字！',
        'maxnum.number' => '标题显示字数填写错误，只能为数字！',
        'listorder.require' => '内容排序选择错误！',
        'listorder.number' => '内容排序选择错误！',
        'plate.require' => '风格样式选择错误！',
        'plate.alphaDash' => '风格样式选择错误！',
        'comment.number' => '是否允许评论选择错误！',
        'pidsee.number' => '在父分类显示选择错误！',
        'password.alphaDash' => '密码只能为字母和数字，下划线_！',
        'num.number' => '简介字数填写错误，只能为数字！',
        '__token__.token' => '已超时，请重试！',

    ];
    protected $scene = [
        'title' => ['title'],
        'add' => ['mid','pid','class','__token__'],
        'edit' => ['id','mid','pid','class','title','maxpage','maxnum','listorder','plate','comment','password','pidsee','__token__'],
        'num' => ['num'],
        'edit1' => ['id','pid','class','title','__token__'],

    ];
}