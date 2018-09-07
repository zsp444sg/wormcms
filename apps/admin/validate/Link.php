<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Link extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'title'  => 'require|unique:link|max:150|min:2|regex:/^[\x{4e00}-\x{9fa5}0-9a-zA-Z-_\s]+$/u',
        'url'  => 'require|unique:link|url',
        'status'  => 'require|number',
        'sort'  => 'require|number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误，请刷新页面！',
        'id.number' => '表单填写错误，请刷新页面！',

        'status.require' => '请选择是否显示！',
        'status.number' => '显示/隐藏选择错误，请重新选择！',

        'sort.require' => '请填写排序值！',
        'sort.number' => '排序值填写错误，请重新填写！',

        'url.require' => '链接地址不得为空！',
        'url.unique' => '链接地址已存在！',
        'url.url' => '链接地址不规范！',

        'title.require' => '链接标题不得为空，请重新填写！',
        'title.unique' => '链接标题已存在，请重新填写！',
        'title.max' => '链接标题为2到50个字符！',
        'title.min' => '链接标题为2到50个字符！',
        'title.regex' => '链接标题不得存在特殊字符！',

        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['title','status','sort','url','__token__'],
        'edit' => ['id','title','url','status','sort','__token__']
    ];
}