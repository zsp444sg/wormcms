<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;
use think\Model;

class Nav extends Model{
    // 启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Nav/see').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-close t-red t-18"></i></a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Nav/see').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-wancheng t-green t-18"></i></a>',2 => '<a title="还原" data-id="'.$data['id'].'" data-href="'.url('Nav/see').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-huanyuan- t-yellow t-18"></i></a>'];
        return $status[$data['status']];
    }
    // 启用状态
    protected function getSeetypezhAttr($value,$data){
        if($data['seetype'] == null){
            $data['seetype'] = '0';
        }
        $seetype = [ 0 => '通用导航',1 => 'PC端导航',2 => '手机端导航',3 => '底部导航'];
        return $seetype[$data['seetype']];
    }
    //  格式化导航
    protected function listnav($data,$pid='0'){
        $option = "<option value='0'>顶级分类</option>";
        if(empty($data)){
            return $option;
        }
        foreach ($data as $v){
            $v['icon'] = '';
            if($v['pid'] != 0){
                $fg = '&nbsp;&emsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
            if($pid !== 0 && $pid == $v['id']){
                $option .= "<option selected value='{$v['id']}'>{$v['icon']}{$v['title']}</option>";
            }else{
                $option .= "<option value='{$v['id']}'>{$v['icon']}{$v['title']}</option>";
            }
        }
        return $option;
    }
    public function listsort($name,$id=null,$pid='0'){
        if(webCacheHas('nav')){
            $listdata = webCacheGet('nav');
        }else{
            $navdata = $this->order('sort desc,id asc')->select();
            foreach ($navdata as $key => $val){
                $val['icon'] = unserialize($val['icon']);
                $listdata[$key] = $val;
            }
            if(empty($listdata)){
                $listdata = array();
            }else{
                webCacheSet('nav',$listdata);
            }
        }
        switch ($name){
            case 'seelist':
                $listsort = $this->zcolumn($listdata);
                break;
            case 'list':
                $listsort = $this->listnav($this->zcolumn($listdata),$pid);
                break;
            case 'uplist':
                $listsort = $this->deldonlist($listdata,$id);
                $listsort = $this->zcolumn($listsort);
                $listsort = $this->listnav($listsort,$pid);
                break;
            case 'del':
                $listsort = $this->donlevel($listdata,$id);
                break;
        }
        return $listsort;
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
    public function _zcolumn($data,$pid = 0,$level = 0){
        static $arr = array();
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $val['level'] = $level + 1;
                $arr[] = $val;
                $this->_zcolumn($data, $val['id'],$val['level']);
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