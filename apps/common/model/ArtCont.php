<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class ArtCont extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime'];
    protected $type = [
        'addtime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'addtime';


}