<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use think\Model;

class FuPartnav extends Model{
    // 启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Nav/see').'" class="button button-S bg-red cx-click" data-type="sestatus">隐藏</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Nav/see').'" class="button button-S bg-green cx-click" data-type="sestatus">显示</a>'];
        return $status[$data['status']];
    }
    //  格式化导航
    protected function listnav($data,$pid='0'){
        foreach ($data as $v){
            $v['icon'] = '';
            if($v['pid'] != 0){
                $fg = '&nbsp;&emsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
        }
        return $data;
    }
    protected function allfupartnav(){
        if(webCacheHas('funav')){
            $listdata = webCacheGet('funav');
        }else{
            $listdata = $this->order('sort desc,id asc')->select();
            webCacheSet('funav',$listdata);
        }
        return $listdata;
    }
    public function listsort($name,$fid,$id=null,$pid='0'){
        $listdata = $this->allfupartnav();
        if(empty($listdata) || !isset($listdata)){
            return true;
        }
        foreach ($listdata as $k => $v){
            if($v['fid'] != $fid){
                unset($listdata[$k]);
            }
        }
        if(empty($listdata) || !isset($listdata)){
            return true;
        }
        switch ($name){
            case 'seelist':
                $listsort = $this->zcolumn($listdata);
                break;
            case 'list':
                $listsort = $this->listnav($this->zcolumn($listdata));
                break;
            case 'uplist':
                $listsort = $this->deldonlist($listdata,$id);
                $listsort = $this->zcolumn($listsort);
                $listsort = $this->listnav($listsort,$pid);
                break;
        }
        return $listsort;
    }
    public function deldata($id){
        $listdata = $this->allfupartnav();
        $deldata = $this->donlevel($listdata,$id);
        return $deldata;
    }
    //  导航分级
    public function zcolumn($data,$pid = 0,$level = 0){
        static $arr = array();
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $val['level'] = $level + 1;
                $arr[] = $val;
                $this->zcolumn($data, $val['id'],$val['level']);
            }
        }
        return $arr;
    }
    protected function deldonlist($data,$id){
        foreach ($data as $k => $v){
            if($id == $v['pid']){
                $arr = $v;
                unset($data[$k]);
                $this->deldonlist($data,$arr['id']);
            }
            if($id == $v['id']){
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function donlevel($data,$pid){
        static $arr = array();
        foreach ($data as $k => $v){
            if($pid == $v['pid']){
                $arr[] = $v->toArray();
                $this->donlevel($data,$v['id']);
            }
        }
        return $arr;
    }
}