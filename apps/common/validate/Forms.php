<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\common\validate;
use think\Validate;
class Forms extends Validate{
    protected $rule =   [
        'fid'  => 'require|number',
        '__token__' => 'token',
    ];

    protected $message  =   [
        'fid.require' => '表单填写错误！',
        'fid.number' => '表单填写错误！',
        '__token__.token' => '已超时，请重新提交！',

    ];
    protected $scene = [
        'add' => ['fid','__token__'],
    ];
}