<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Forms extends Validate{
    protected $rule =   [
        'fid'  => 'require|number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'id.require' => '表单填写错误！',
        'id.number' => '表单填写错误！',
        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['fid'],
    ];
}