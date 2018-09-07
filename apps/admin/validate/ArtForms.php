<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class ArtForms extends Validate{
    protected $rule =   [
        'id'  => 'require|number',
        'title'  => 'unique:art_forms|require|length:2,150',

        'tourist' => 'require|number',
        'status' => 'require|number',
        'sort' => 'require|number',

        'num' => 'require|number',


        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误！',
        'id.number' => '表单填写错误！',

        'title.unique' => '表单名称已存在！',
        'title.require' => '表单名称不得为空！',
        'title.length' => '表单名称字数为4-50个文字！',

        'num.require' => '用户组选择错误！',
        'num.number' => '用户组选择错误！',

        'tourist.require' => '是否允许游客填写选择错误！',
        'tourist.number' => '是否允许游客填写选择错误！',
        'status.require' => '是否启用选择错误！',
        'status.number' => '是否启用选择错误！',
        'sort.require' => '排序值不得为空！',
        'sort.number' => '排序值只能为数字！',
        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['title','tourist','status','sort','__token__'],
        'edit' => ['id','title','tourist','status','sort','__token__'],
        'num' => ['num'],
        'field' => ['id'],
    ];
}