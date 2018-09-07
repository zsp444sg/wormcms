<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 15:44
 */
namespace app\admin\validate;
use think\Validate;
class Label extends Validate{
    protected $rule =   [
        '__token__' => 'token',
    ];

    protected $message  =   [
        '__token__.token' => '已超时，请重试！',
    ];
    protected $scene = [
        'add' => ['__token__'],
    ];
}