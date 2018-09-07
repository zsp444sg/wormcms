<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 11:48
 */
namespace app\admin\model;
use think\Db;
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
        $status = [ 0 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/see').'" class="button button-S bor-red cx-click" data-type="sestatus"><i class="cx-icon cx-icon-close"></i></a>',1 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/see').'" class="button button-S bor-green cx-click" data-type="sestatus"><i class="cx-icon cx-icon-wancheng"></i></a>',2 => '<a data-id="'.$data['uid'].'" data-href="'.url('User/dsee').'" class="button button-S bor-green cx-click mr-5" data-type="sestatus"><i class="cx-icon cx-icon-huanyuan-"></i></a><a data-id="'.$data['uid'].'" data-href="'.url('User/del').'" class="button button-S bor-red cx-click mr-5" data-type="deldata"><i class="cx-icon cx-icon-lajixiang"></i></a>'];
        return $status[$data['status']];
    }
    protected function getStatusurlAttr($value,$data){
        $status = [ 0 => '<a class="pointer c-error"><i class="cx-icon cx-icon-close"></i></a>',1 => '<a class="pointer c-success"><i class="cx-icon cx-icon-wancheng"></i></a>',2 => '<a class="pointer c-danger"><i class="cx-icon cx-icon-lajixiang"></i></a>'];
        return $status[$data['status']];
    }
    public function user(){
        return $this->belongsTo('User','uid');
    }
    //  写入用户数据表
    public function addfield($data,$add = 'add'){
        if($data['sqltype'] == 'varchar'){
            $sqltype = "varchar(255) not null";
        }elseif($data['sqltype'] == 'int'){
            $sqltype = 'int(10) not null';
        }elseif($data['sqltype'] == 'mediumtext'){
            $sqltype = 'mediumtext not null';
        }else{
            return false;
        }
        $tabelname = config('database.prefix').'user_data';
        $sql = "alter table `{$tabelname}` {$add} `{$data['sqlname']}` {$sqltype}";
        $addfield = Db::execute($sql);
        return $addfield;
    }
    //  打包数据并写入配置文件
    public function zipdata($data,$fieldname = ''){
        $webdb = webdb();
        $array = unserialize($webdb['userdata']);
        if($fieldname && !empty($array['field_db']) && isset($array['field_db'])){
            unset($array['field_db'][$fieldname]);
        }
        $array['field_db'][$data['sqlname']]=$data;
        if($data['formtype']=='upfile'){
            $array['is_upfile'][$fieldname]=$data['sqlname'];
        }else{
            unset($array['is_upfile'][$fieldname]);
        }
        $array['field_db'] = $this->listfield($array['field_db']);
        return $array;
    }
    //  处理排序
    public function listfield($data){
        $keysvalue = $new_array = array();
        foreach ($data as $k => $v){
            $keysvalue[$k] = $v['sort'];
        }
        arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $data[$k];
        }
        return $new_array;
    }

}