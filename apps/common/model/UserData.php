<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 11:48
 */
namespace app\common\model;
use think\Model;

class UserData extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['uregtime','uregip'];
    protected $update = ['ulogtime','ulogip'];
    protected $type = [
        'uregtime'    => 'timestamp',
        'ulogtime'    => 'timestamp',
    ];
// 定义时间戳字段名
    protected $createTime = 'uregtime';
    protected $updateTime = 'ulogtime';
    protected function setUregipAttr(){
        return request()->ip();
    }
    protected function setUlogipAttr(){
        return request()->ip();
    }
    // 启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/see').'" class="c-error cx-click" data-type="sestatus"><i class="fa fa-minus-circle"></i></a>',1 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/see').'" class="c-success cx-click" data-type="sestatus"><i class="fa fa-check-circle"></i></a>',2 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/dsee').'" class="cx-click mr-5" data-type="sestatus"><i class="fa fa-mail-reply-all c-success"></i></a><a data-id="'.$data['uid'].'" data-href="'.url('User/del').'" class="cx-click mr-5" data-type="deldata"><i class="fa fa-trash-o c-danger"></i></a>'];
        return $status[$data['status']];
    }
    protected function getStatusurlAttr($value,$data){
        $status = [ 0 => '<a class="pointer c-error"><i class="fa fa-minus-circle"></i></a>',1 => '<a class="pointer c-success"><i class="fa fa-check-circle"></i></a>',2 => '<a class="pointer c-danger"><i class="fa fa-trash-o"></i></a>'];
        return $status[$data['status']];
    }
    public function user(){
        return $this->belongsTo('User','uid');
    }

}