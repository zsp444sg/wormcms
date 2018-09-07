<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class UserLogin extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['loginip','logintime'];
    protected $type = [
        'logintime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'logintime';
    protected $updateTime = false;
    protected function setLoginipAttr(){
        return request()->ip();
    }


}