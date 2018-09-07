<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class Special extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime'];
    protected $update = ['edittime'];
    protected $type = [
        'addtime'    => 'timestamp',
        'edittime'    => 'timestamp',
    ];
// 定义时间戳字段名
    protected $createTime = 'addtime';
    protected $updateTime = 'edittime';

    // 启用状态
    protected function getStatuszanAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Special/see').'" class="c-danger cx-click" data-type="sestatus"><i class="fa fa-minus-circle"></i></a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Special/see').'" class="c-success cx-click" data-type="sestatus"><i class="fa fa-check-circle"></i></a>'];
        return $status[$data['status']];
    }
    // 推荐状态
    protected function getJianzhAttr($value,$data){
        $jian = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Special/jian').'" class="c-999 cx-click" data-type="sestatus"><i class="fa fa-thumbs-o-up"></i></a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Special/jian').'" class="c-warning cx-click" data-type="sestatus"><i class="fa fa-thumbs-o-up"></i></a>'];
        return $jian[$data['jian']];
    }
    //  查询是否存在内容并返回
    public function donlevel($id){
        $cont = model('Article')->where('sid',$id)->count();
        if($cont > 0){
            return false;
        }
        return true;
    }
    public function artspecial($data){
        return true;
    }

}