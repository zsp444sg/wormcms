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
class ArtForms extends Model{
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
    // 启用状态
    protected function getStatuszanAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('ArtForms/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('ArtForms/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    // 启用状态
    protected function getTouristzhAttr($value,$data){
        $tourist = [ 0 => '<a class="button button-S bor-red">不允许</a>',1 => '<a class="button button-S bor-green">允许</a>'];
        return $tourist[$data['tourist']];
    }
    //  新建数据表
    public function addtable($data){
        $tabelname = config('database.prefix').'forms_cont_'.$data['id'];
        Db::execute("CREATE TABLE {$tabelname} (`id` mediumint(8) NOT NULL auto_increment,`uid` mediumint(7) NOT NULL default '0',`username` varchar(255) NOT NULL default '0',`addtime` int(10) NOT NULL default '0',`addip` varchar(15) NOT NULL default '0',`edittime` int(10) NOT NULL default '0',`editip` varchar(15) NOT NULL default '0',`status` int(0) NOT NULL default '0',`reply` mediumtext,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    }
    //  删除模型对应数据表
    public function delfield($data){
        $tabelname = config('database.prefix').'forms_cont_'.$data['id'];
        $sql = "drop table `{$tabelname}`";
        Db::execute($sql);
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
        $tabelname = config('database.prefix').'forms_cont_'.$data['id'];
        $sql = "alter table `{$tabelname}` {$add} `{$data['sqlname']}` {$sqltype}";
        $addfield = Db::execute($sql);
        return $addfield;
    }
    //  打包数据并写入配置文件
    public function zipfield($data,$fieldname = ''){
        $array = $this->get($data['id']);
        $array = unserialize($array['conf']);
        if($fieldname && !empty($array['field_db']) && isset($array['field_db'])){
            unset($array['field_db'][$fieldname]);
        }
        $config['id'] = $data['id'];
        unset($data['id']);
        $array['field_db'][$data['sqlname']] = $data;
        if($data['formtype']=='ieedit'){
            $array['is_html'][$fieldname] = $data['sqlname'];
        }else{
            unset($array['is_html'][$fieldname]);
        }
        if($data['formtype']=='upfile'){
            $array['is_upfile'][$fieldname] = $data['sqlname'];
        }else{
            unset($array['is_upfile'][$fieldname]);
        }
        $array['field_db'] = $this->listfield($array['field_db']);
        $config['conf']=serialize($array);
        $this->save($config,$config['id']);
        return $array;
    }
    //  处理字段排序
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
    //  查询所有模型信息并写入缓存
    public function artmodel(){
        if(webCacheHas('artform')){
            $artmodel = webCacheGet('artform');
        }else{
            $artmodel = $this->order('sort desc,id asc')->select();
            webCacheSet('artform',$artmodel);
        }
        return $artmodel;
    }
    //  获取模型配置
    public function formconf($fid){
        $artmodel = $this->artmodel();
        foreach ($artmodel as $v){
            if($v['id'] == $fid){
                return $v;
                break;
            }
        }
        return false;
    }

}