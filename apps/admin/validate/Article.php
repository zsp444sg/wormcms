<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Article extends Validate{
    protected $rule =   [
        'aid'  => 'require|number',
        'fid'  => 'require|number',
        'status'  => 'number',
        'jian'  => 'number',
        'top'  => 'number',
        'getpic'  => 'number',
        'geturl'  => 'number',
        'mid'  => 'require|number',
        'hist'  => 'number',

        'sid'  => 'array',
        'fuid'  => 'array',
        'template'  => 'array',
        'seegroup'  => 'array',
        'dontgroup'  => 'array',

        'plate'  => 'alphaDash',

        'title'  => 'unique:article|require|max:255|min:4',
        'keywords'  => 'max:255|min:4',

        'author'  => 'max:255|min:2',
        'source'  => 'max:255|min:2',
        'sourceurl'  => 'url',
        'jumpurl'  => 'url',

        'num' => 'number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'aid.require' => '表单填写错误，请刷新页面！',
        'aid.number' => '表单填写错误，请刷新页面！',

        'title.unique' => '来源已存在，请重新填写！',
        'title.require' => '来源不得为空！',
        'title.max' => '来源为2到255个字符！',
        'title.min' => '来源为2到255个字符！',

        'url.url' => 'url如：http://www.cxbs.net！',

    ];
    protected $scene = [
        'add' => ['fid','status','jian','top','getpic','geturl','hist','sid','fuid','template','seegroup','dontgroup','plate','keywords','author','source','sourceurl','jumpurl'],
        'edit' => ['aid','fid','status','jian','top','getpic','geturl','hist','sid','fuid','template','seegroup','dontgroup','plate','keywords','author','source','sourceurl','jumpurl','__token__'],
        'num' => ['num'],
    ];
}