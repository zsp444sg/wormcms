<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Db;
use think\Model;
class Forms extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime','addip'];
    protected $update = ['edittime','editip'];
    protected $type = [
        'addtime'    => 'timestamp',
        'edittime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'addtime';
    protected $updateTime = 'edittime';
    protected function setAddipAttr(){
        return request()->ip();
    }
    protected function setEditipAttr(){
        return request()->ip();
    }
    //  获取附加表字段
    public function futabel($fid){
        $tabelname = config('database.prefix').'forms_cont_'.$fid;
        $fields = Db::query("DESC $tabelname");
        $arr = '';
        foreach ($fields as $v){
            $arr[] = $v['Field'];
        }
        return $arr;
    }


}