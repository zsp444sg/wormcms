<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use think\Model;

class LogsAdlogin extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['loginip','logintime'];
    protected $type = [
        'logintime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'logintime';
    protected function setLoginipAttr(){
        return request()->ip();
    }
}