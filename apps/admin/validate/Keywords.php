<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Keywords extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'title'  => 'unique:keywords|require|max:255|min:4',
        'url' => 'url',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误，请刷新页面！',
        'id.number' => '表单填写错误，请刷新页面！',

        'title.unique' => '关键词已存在，请重新填写！',
        'title.require' => '关键词不得为空！',
        'title.max' => '关键词为2到255个字符！',
        'title.min' => '关键词为2到255个字符！',

        'url.url' => 'url如：http://www.cxbs.net！',

    ];
    protected $scene = [
        'add' => ['title','url'],
        'edit' => ['id','title','url'],

    ];
}