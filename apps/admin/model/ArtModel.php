<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\admin\model;
use think\Db;
use think\Model;
class ArtModel extends Model{
    // 启用状态
    protected function getStatuszanAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('ArtModel/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('ArtModel/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    //  查询所有模型信息并写入缓存
    public function artmodel(){
        if(webCacheHas('artmodel')){
            $artmodel = webCacheGet('artmodel');
        }else{
            $artmodel = $this->order('sort desc,id asc')->select();
            foreach ($artmodel as $k => $v){
                $artmodel[$k] = $v;
            }
            webCacheSet('artmodel',$artmodel);
        }
        return $artmodel;
    }
    //  打包模型基本信息
    public function zipmodel($data){
        $modelarr = $this->get($data['id']);
        if($modelarr){
            $array['config'] = unserialize($modelarr['config']);
        }
        $array['config']['model_db'] = $data;
        $array = serialize($array['config']);
        return $array;
    }
    //  打包数据并写入配置文件
    public function zipfield($data,$fieldname = ''){
        $array = $this->get($data['id']);
        $array = unserialize($array['config']);
        if($fieldname){
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
        $config['config']=serialize($array);
        $this->save($config,$config['id']);
        return $array;
    }
    //  新建数据表
    public function addtable($data){
        $tabelname = config('database.prefix').'article_content_'.$data['id'];
        Db::execute("CREATE TABLE {$tabelname} (`id` mediumint(8) NOT NULL auto_increment,`aid` int(10) NOT NULL default '0',`fid` mediumint(7) NOT NULL default '0',`uid` mediumint(7) NOT NULL default '0',PRIMARY KEY  (`id`),KEY `fid` (`fid`),KEY `uid` (`uid`),KEY `aid` (`aid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
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
    //  写入用户数据表
    public function addfield($data,$add = 'add'){
        if($data['sqltype'] == 'varchar'){
            $sqltype = "varchar(255) not null";
        }elseif($data['sqltype'] == 'int'){
            $sqltype = 'int(10) not null';
        }elseif($data['sqltype'] == 'mediumtext'){
            $sqltype = 'mediumtext not null';
        }else{
            $this->error('数据类型错误，请重新选择！');
        }
        $tabelname = config('database.prefix').'article_content_'.$data['id'];
        $sql = "alter table `{$tabelname}` {$add} `{$data['sqlname']}` {$sqltype}";
        $addfield = Db::execute($sql);
        return $addfield;
    }
    //  删除模型对应数据表
    public function delfield($data){
        $tabelname = config('database.prefix').'article_content_'.$data['id'];
        $sql = "drop table `{$tabelname}`";
        $addfield = Db::execute($sql);
    }
    //  获取模型信息
    public function allmodel(){
        $allmodel = $this->where('status',1)->order('sort desc,id asc')->select();
        return $allmodel;
    }
    //  获取模型属性
    public function modelbase($id){
        if(!request()->param()){
            $this->error("没有对应的模型信息！");
        }
        $moddata = $this->where('id',$id)->value('config');
        $moddata = unserialize($moddata);
        $modelbase = $moddata['model_db'];
        return $modelbase;
    }
    //  获取模型字段信息
    public function modelfield($id){
        if(!request()->param()){
            $this->error("没有对应的模型信息！");
        }
        $moddata = $this->where('id',$id)->value('config');
        $moddata = unserialize($moddata);
        $modelbase = $moddata['model_db'];
        return $modelbase;
    }


}