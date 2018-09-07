<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class FuPartnav extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'pid'  => 'require|number',
        'status'  => 'require|number',
        'sort'  => 'require|number',
        'title'  => 'max:150|min:4|regex:/^[\x{4e00}-\x{9fa5}0-9a-zA-Z-_\s]+$/u',
        'url'  => 'require',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误，请刷新页面！',
        'id.number' => '表单填写错误，请刷新页面！',
        'pid.require' => '请选择上级分类！',
        'pid.number' => '请选择上级分类！',
        'title.unique' => '栏目已存在，请重新填写！',
        'title.max' => '栏目名称为2到50个字符！',
        'title.min' => '栏目名称为2到50个字符！',
        'title.regex' => '栏目名称不得存在特殊字符！',

        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['pid','title','status','sort','url','__token__'],
        'edit' => ['id','pid','title','status','sort','url','__token__'],
    ];
}