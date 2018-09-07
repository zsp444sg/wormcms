<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-02
 * Time: 9:02
 */
namespace app\common\model;
use think\Model;
class UserSpread extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime','addip'];
    protected $type = [
        'addtime'    => 'timestamp',
    ];
// 定义时间戳字段名
    protected $createTime = 'addtime';
    protected $updateTime = false;
    protected function setAddipAttr(){
        return request()->ip();
    }

}