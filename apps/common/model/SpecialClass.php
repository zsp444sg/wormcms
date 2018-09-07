<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class SpecialClass extends Model{
    protected function listnav($data,$pid='0'){
        $option = "<option value='0'>顶级分类</option>";
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
    //  获取专题分类
    public function pids($pid = '0'){
        $title = $this->order('sort desc,id asc')->select();
        $title = $this->_zcolumn($title);
        $option = $this->listnav($title);
        return $option;
    }
    //  去除下级子栏目
    public function deldonlist($id,$pid){
        $list = $this->order('sort desc,id asc')->select();
        $arr = $this->_deldonlist($list,$id);
        $data = $this->_zcolumn($arr);
        $data = $this->listnav($data,$pid);
        return $data;
    }
    protected function _deldonlist($data,$id){
        foreach ($data as $k => $v){
            if($id == $v['pid']){
                $arr = $v;
                unset($data[$k]);
                $this->_deldonlist($data,$arr['id']);
            }
            if($id == $v['id']){
                unset($data[$k]);
            }
        }
        return $data;
    }
    //  栏目分级
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
    //  删除下级分类 查询是否有内容
    public function donlevel($id){
        $don = $this->field('id,pid')->select();
        $don = $this->_donlevel($don,$id);
        return $don;
    }
    protected function _donlevel($data,$pid){
        static $arr = array();
        foreach ($data as $k => $v){
            if($pid == $v['pid']){
                $arr[] = $v->toArray();
                $this->_donlevel($data,$v['id']);
            }
        }
        return $arr;
    }
    //  查询分类下是否存在专题
    public function delarticle($id){
        $con = model('Special')->where('class',$id)->count();
        if($con > 0){
            return false;
        }
        return true;
    }

}