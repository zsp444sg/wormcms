<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;
use think\Model;

class Part extends Model{
    // 显示状态
    protected function getClasszhAttr($value,$data){
        $class = [ 0 => '<a href="'.url('Part/donpart',array('pid'=>$data['id'],'mid'=>$data['mid'])).'" class="button button-S bor-blue">大分类</a>',1 => '<a class="button button-S bor-a">子栏目</a>',2 => '<a class="button button-S bor-a">单篇文章</a>'];
        return $class[$data['class']];
    }
    // 编辑按钮
    protected function getClasseditAttr($value,$data){
        $class = [ 0 => "<a class='button button-S bg-green' href='".url('Part/edit',array('mid'=>$data['mid'],'id'=>$data['id']))."'><i class='cx-icon cx-icon-shezhi'></i></a>",1 => "<a class='button button-S bg-green' href='".url('Part/edit',array('mid'=>$data['mid'],'id'=>$data['id']))."'><i class='cx-icon cx-icon-shezhi'></i></a>",2 => "<a class='button button-S bg-green' href='".url('Part/fuedit',array('mid'=>$data['mid'],'id'=>$data['id']))."'><i class='cx-icon cx-icon-shezhi'></i></a>"];
        return $class[$data['class']];
    }
    // 编辑状态
    protected function getClasscontAttr($value,$data){
        $class = [ 0 => "",1 => "<a class='button button-S bg-green' href='".url('Article/index',array('mid'=>$data['mid'],'fid'=>$data['id']))."'>管理</a>",2 => "<a class='button button-S bg-green' href='".url('Part/fuedit',array('id'=>$data['id']))."'>编辑</a>"];
        return $class[$data['class']];
    }
    // 显示状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Part/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Part/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    //  获取所有分类并进行排序
    protected function partlist(){
        $partlist = $this->order('sort desc,id asc')->select();
        return $partlist;
    }
    /**
     * @param string $mid
     * @param string $pid
     * @param string $only
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function artmodels($mid='0',$only='0',$pid='0'){
        if(webCacheHas('artmodel')){
            $artmodels = webCacheGet('artmodel');
        }else{
            $artmodels = \model('ArtModel')->order('sort desc,id asc')->select();
            webCacheSet('artmodel',$artmodels);
        }
        if(!$artmodels){
            return "<select name='mid' required lay-filter='formtype'><option value='0'>文章模型</option></select>";
        }

        if($mid == '0'){
            $option = "<option selected='selected' value='0'>文章模型</option>";
        }else{
            $option = "<option value='0'>文章模型</option>";
        }
        if($only == '0' && $pid != '0'){
            if($mid != '0'){
                $option = "<option disabled value='0'>文章模型</option>";
            }
            $disabled = 'disabled';
        }else{
            $disabled = '';
        }
        foreach ($artmodels as $k => $v){
            if($v['status'] == '0'){
                continue;
            }
            if ($mid == $v['id']){
                $option .= "<option selected='selected' value='{$v['id']}'>{$v['title']}</option>";
            }else{
                $option .= "<option {$disabled} value='{$v['id']}'>{$v['title']}</option>";
            }
        }
        $option = "<select name='mid' required lay-filter='formmids'>{$option}</select>";
        return $option;
    }

    /**
     * @param $name
     * @param string $only
     * @param null $data
     * @return bool|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function sort($name,$data = null,$only='0'){
        $sortdata = $this->partlist();
        foreach ($sortdata as $key => $val){
            if($only == '0'){
                if($val['mid'] != $data['mid']){
                    continue;
                }
            }
            switch ($name){
                case 'list':
                    if(!empty($data['mid']) || isset($data['mid'])){
                        if($val['mid'] != $data['mid'] && $val['pid'] == '0'){
                            continue;
                        }
                    }
                    $partClass[] = $val;
                    break;
                case 'uplist':
                    if($val['class'] != '0'){
                        continue;
                    }
                    $partClass[] = $val;
                    break;
                case 'deldonlist':
                    $partClass = $this->deldonlist($sortdata,$data['id'],$data['mid'],$only);
                    break;
                case 'donLevel':
                    $partClass = $this->zcolumn($sortdata,$data['id']);
                    return $partClass;
                    break;
            }
        }
        if(empty($partClass) || !isset($partClass)){
            return true;
        }
        $sortdata = $this->zcolumn($partClass);
        foreach ($sortdata as $k => $v){
            if($v['pid'] != 0){
                $fg = '|&nbsp;&nbsp;&nbsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
            $v['articlenum'] = model('Article')->where('fid',$v['id'])->count();
        }
        return $sortdata;
    }
    //  去除下级栏目
    protected function deldonlist($data,$id,$mid,$only){
        foreach ($data as $k => $v){
            if($v['class'] != '0'){
                unset($data[$k]);
                continue;
            }
            if($only == '0'){
                if($v['mid'] != $mid){
                    unset($data[$k]);
                    continue;
                }
            }
            if($id == $v['pid']){
                $arr = $v;
                unset($data[$k]);
                $this->deldonlist($data,$arr['id'],$arr['mid'],$only);
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
    //  文章页调用栏目信息
    public function artlist($data,$only='0'){
        $sortdata = $this->partlist();
        foreach ($sortdata as $k => $v){
            if($only == '0'){
                if($v['mid'] != $data['mid']){
                    unset($sortdata[$k]);
                    continue;
                }
            }
            if($v['class'] == '2'){
                unset($sortdata[$k]);
                continue;
            }
        }
        $sortdata = $this->zcolumn($sortdata);
        $option = '';
        foreach ($sortdata as $k => $v){
            $v['icon'] = '';
            if($v['pid'] != 0){
                $fg = '|&nbsp;&nbsp;&nbsp;';
                $v['icon'] = str_repeat($fg, $v['level']-1).'|--';
            }
            if($v['class'] == '0'){
                $option .= "<option disabled value='{$v['id']}'>{$v['icon']}{$v['title']}</option>";
                continue;
            }
            if(!empty($data['fid']) && isset($data['fid']) && $v['id'] == $data['fid']){
                $option .= "<option selected value='{$v['id']}'>{$v['icon']}{$v['title']}</option>";
            }else{
                $option .= "<option value='{$v['id']}'>{$v['icon']}{$v['title']}</option>";
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
            if(is_dir($gtempdir.$plate.'/largepart')){
                $tempdir = $gtempdir.$plate.'/largepart/';
            }else{
                $tempdir = $gtempdir.$default.'/largepart/';
            }
        }else{
            if(is_dir($gtempdir.$plate.'/smallpart')){
                $tempdir = $gtempdir.$plate.'/smallpart/';
            }else{
                $tempdir = $gtempdir.$default.'/smallpart/';
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
    public function donedit($data,$only = true){
        $dondata = $this->where('pid',$data['id'])->select();
        foreach ($dondata as $v){
            $v['level'] = $data['level'] + 1;
            if($only == false){
                $v['mid'] = $data['mid'];
            }
            $arr = $v->toArray();
            $this->update($arr);
            if($v['class'] == '0'){
                $this->donedit($v,$only);
            }
        }
        return true;
    }
}