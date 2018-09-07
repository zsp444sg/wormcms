<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;
use think\Model;

class FuPart extends Model{
    // 显示状态
    protected function getClasszhAttr($value,$data){
        $class = [ 0 => '<a href="'.url('FuPart/donpart',array('pid'=>$data['id'])).'" class="button button-S bor-blue">大分类</a>',1 => '<a class="button button-S bor-a">子栏目</a>'];
        return $class[$data['class']];
    }
    // 编辑按钮
    protected function getClasseditAttr($value,$data){
        $class = [ 0 => "<a class='button button-S bg-green' href='".url('FuPart/edit',array('id'=>$data['id']))."'><i class='cx-icon cx-icon-shezhi'></i></a>",1 => "<a class='button button-S bg-green' href='".url('FuPart/edit',array('id'=>$data['id']))."'><i class='cx-icon cx-icon-shezhi'></i></a>"];
        return $class[$data['class']];
    }
    // 编辑状态
    protected function getClasscontAttr($value,$data){
        $class = [ 0 => "",1 => "<a class='button button-S bg-green' href='".url('Article/index',array('fuid'=>$data['id']))."'>管理</a>",2 => "<a class='button button-S bg-green' href='".url('FuPart/fuedit',array('id'=>$data['id']))."'>编辑</a>"];
        return $class[$data['class']];
    }
    // 显示状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('FuPart/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('FuPart/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    // 独立导航
    protected function getFunavbtnAttr($value,$data){
        $funav = [ 0 => '<a class="button button-S bg-red">禁用</a>',1 => '<a class="button button-S bg-blue" href="'.url('FuPartnav/index',array('fid'=>$data['id'])).'">编辑</a>'];
        return $funav[$data['funav']];
    }
    //  获取所有分类并进行排序
    protected function partlist(){
        $partlist = $this->order('sort desc,id asc')->select();
        return $partlist;
    }

    /**
     * @param $name
     * @param string $only
     * @param null $data
     * @return bool|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function sort($name,$data = null){
        $sortdata = $this->partlist();
        if(empty($sortdata) || !isset($sortdata)){
            return false;
        }
        foreach ($sortdata as $key => $val){
            switch ($name){
                case 'list':
                    $partClass[] = $val;
                    break;
                case 'uplist':
                    if($val['class'] != '0'){
                        continue;
                    }
                    $partClass[] = $val;
                    break;
                case 'deldonlist':
                    $partClass = $this->deldonlist($sortdata,$data['id']);
                    break;
                case 'donLevel':
                    $partClass = $this->zcolumn($sortdata,$data['id']);
                    return $partClass;
                    break;
            }
        }
        $sortdata = $this->zcolumn($partClass);
        foreach ($sortdata as $k => $v){
            if($v['pid'] != 0){
                $fg = '|&nbsp;&nbsp;&nbsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
            $v['articlenum'] = model('Article')->where('','EXP',"FIND_IN_SET({$v['id']},fuid)")->count();
        }
        return $sortdata;
    }
    //  去除下级栏目
    protected function deldonlist($data,$id){
        foreach ($data as $k => $v){
            if($v['class'] != '0'){
                unset($data[$k]);
                continue;
            }
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
    //  栏目打包
    public function zcolumn($data,$pid = 0){
        static $arr = array();
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $arr[] = $val;
                $this->zcolumn($data, $val['id']);
            }
        }
        return $arr;
    }
    //  文章页面调用辅助栏目信息
    public function artfulist($data){
        $sortdata = $this->partlist();
        $sortdata = $this->zcolumn($sortdata);
        $fuids = $option = '';
        if(!empty($data['fuid']) || isset($data['fuid'])){
            $fuids = explode(',',$data['fuid']);
        }
        foreach ($sortdata as $k => $v){
            $v['icon'] = '';
            if($v['pid'] != 0){
                $fg = '|&nbsp;&nbsp;&nbsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
            if($v['class'] == '0'){
                $option[] = "<input type='checkbox' name='fuid[]' title='{$v['icon']}{$v['title']}' value='{$v['id']}' lay-skin='primary' disabled>";
                continue;
            }
            if(!empty($data['fuid']) && isset($data['fuid']) && in_array($v['id'],$fuids)){
                $option[] = "<input type='checkbox' name='fuid[]' title='{$v['icon']}{$v['title']}' value='{$v['id']}' lay-skin='primary' checked>";
            }else{
                $option[] = "<input type='checkbox' name='fuid[]' title='{$v['icon']}{$v['title']}' value='{$v['id']}' lay-skin='primary'>";
            }
        }
        return $option;
    }
    //  获取栏目样式
    public function temps($temps,$data){
        $commonconf = require(CONF_PATH.'config.php');
        $gtempdir = $commonconf['template']['view_path']; //获取默认风格目录
        $default = $plate = model('Config')->where('conf','temp')->value('conf_value');
        if($data['plate'] !== 0 && $data['plate'] !== null){
            //获取自订义风格目录
            if(is_dir($gtempdir.$data['plate'])){
                $plate = $data['plate'];
            }
        }
        if($default == 0 || $default == null){
            $default = 'default';
        }
        //  读取样式目录
        if($data['class'] == 0){
            if(is_dir($gtempdir.$plate.'/fulargepart')){
                $tempdir = $gtempdir.$plate.'/fulargepart/';
            }else{
                $tempdir = $gtempdir.$default.'/fulargepart/';
            }
        }else{
            if(is_dir($gtempdir.$plate.'/fusmallpart')){
                $tempdir = $gtempdir.$plate.'/fusmallpart/';
            }else{
                $tempdir = $gtempdir.$default.'/fusmallpart/';
            }
        }
        $tempword = scandir($tempdir);
        $f1 = "默认样式";
        $imgurl = "{$tempdir}0.jpg";
        $select = "<option data-img='{$tempdir}0.jpg' value='0'>$f1</option>";
        foreach ($tempword as $v){
            if($v == '.' || $v == '..'){
                continue;
            }
            if(preg_match('/.htm/',$v)){
                $v = str_replace(".htm","",$v);
                if($temps == $v){
                    $imgurl = "{$tempdir}{$v}.jpg";
                    $select .= "<option selected='selected' data-img='{$imgurl}' value='{$v}'>{$v}</option>";
                }else{
                    $select .= "<option data-img='{$tempdir}{$v}.jpg' value='{$v}'>{$v}</option>";
                }
            }

        }
        $temps = "<select name='listtqmp[temp]' lay-filter='listtqmp'>{$select}</select><div class='xs3 xl12 mt-10'><img id='listtqmp-img' src='{$imgurl}' width='50%' height='auto' onerror=\"this.src='/cxadmin/images/imgnone.png'\"> </div>";
        return $temps;
    }
    //  更改下级栏目属性
    public function donedit($data){
        $dondata = $this->where('pid',$data['id'])->select();
        foreach ($dondata as $v){
            $v['level'] = $data['level'] + 1;
            $arr = $v->toArray();
            $this->update($arr);
            if($v['class'] == '0'){
                $this->donedit($v);
            }
        }
        return true;
    }





}