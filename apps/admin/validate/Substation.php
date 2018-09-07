<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Substation extends Validate{

    protected $rule =   [
        'id'  => 'require|number',

        'status'  => 'require|number',
        'sort'  => 'require|number',

        'title'  => 'unique:substation|require',
        'dtitle'  => 'unique:substation',

        'erurl'  => 'unique:substation|url',

        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误，请刷新页面！',
        'id.number' => '表单填写错误，请刷新页面！',
        'status.require' => '请选择是否启用分站！',
        'status.number' => '表单填写错误，请刷新页面！',
        'sort.require' => '排序值不得为空！',
        'sort.number' => '排序值只能是数字！',

        'title.unique' => '分站名称已存在，请重新填写！',
        'title.require' => '分站名称不得为空！',
        'dtitle.unique' => '分站简称已存在，请重新填写！',
        'erurl.unique' => '二级域名已存在，请重新填写！',
        'erurl.url' => '二级域名不是有效域名！',

        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['erurl','title','dtitle','sort','status','__token__'],
        'edit' => ['id','erurl','title','dtitle','sort','status','__token__'],
    ];
}