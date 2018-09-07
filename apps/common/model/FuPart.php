<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use think\Model;

class FuPart extends Model{
    //  获取所有分类并进行排序
    protected function partlist(){
        if(webCacheHas('fupart')){
            $partlist = webCacheGet('fupart');
        }else{
            $partlist = $this->order('sort desc,id asc')->select();
            webCacheSet('fupart',$partlist);
        }
        return $partlist;
    }
    //  获取栏目信息
    public function classpart($data){
        $partlist = $this->partlist();
        foreach ($partlist as $val){
            if($val['id'] == $data['fuid']){
                $classpart = $val;
                break;
            }
        }
        return $classpart;
    }
    //  获取大分类下的所有小栏目
    public function largelist($fid){
        $partlist = $this->partlist();
        $largelist = $this->zcolumn($partlist,$fid);
        return $largelist;
    }
    //  栏目打包
    protected function zcolumn($data,$pid = '0'){
        static $zcolumn = array();
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $zcolumn[] = $val->toArray();
                $this->zcolumn($data, $val['id']);
            }
        }
        return $zcolumn;
    }
    //  获取所有上级栏目
    public function menus($pid){
        $partlist = $this->partlist();
        $menus = $this->upcolumn($partlist,$pid);
        return $menus;
    }
    //  查找所有上级栏目
    public function upcolumn($data,$pid = '0'){
        static $upcolumn = array();
        foreach ($data as $val) {
            if ($val['id'] == $pid) {
                $upcolumn[] = $val;
                if($val['class'] == '0'){
                    break;
                }
                $this->upcolumn($data, $val['pid']);
            }
        }
        return $upcolumn;
    }
    //  查找顶级上级栏目
    public function uponecolumn($data,$pid = '0'){
        static $upcolumn = array();
        foreach ($data as $val) {
            if ($val['id'] == $pid) {
                if($val['pid'] == '0'){
                    $upcolumn = $val;
                    break;
                }
                $this->uponecolumn($data, $val['pid']);
            }
        }
        return $upcolumn;
    }
    //  查找顶级栏目
    public function levelpart($id){
        $partlist = $this->partlist();
        $levelpart = $this->uponecolumn($partlist,$id);
        return $levelpart;
    }
    //  查找单一栏目
    public function onepart($id){
        $partlist = $this->partlist();
        foreach ($partlist as $val) {
            if ($val['id'] == $id) {
                return $val;
            }
        }
        return;
    }

}