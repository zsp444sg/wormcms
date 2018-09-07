<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-01-19
 * Time: 16:04
 */
namespace app\common\model;

use think\Cache;
use think\Model;

class Label extends Model{

    public function contval($data){
        $title = $this->where('plate',$data['plate'])->where('mlabel',$data['mlabel'])->where('status','1')->select();
        $fhdata = null;
        foreach ($title as $k => $v){
            $v['parts'] = explode(',',$v['parts']);
            if(in_array($data['parts'],$v['parts'])){
                $v['parts'] = implode(',',$v['parts']);
                $fhdata[$v['title']] = $v->toArray();
            }else{
                $v['parts'] = implode(',',$v['parts']);
                $fhdata[$v['title']] = $v->toArray();
            }
        }
        return $fhdata;
    }

    /**
     * @param $data 要处理的数据
     * @param string $fids  需要展示的栏目名称
     * @param bool $upcheckbox  大分类是否可选，默认不可选
     * @return array|string 返回多选框列表
     */
    public function fuidsbase($data,$fids='',$upcheckbox=false){
        $fuids = explode(',',$fids);
        $arr = $checkeds = $disabled = '';
        foreach ($data as $k => $v){
            $v['icon'] = '';
            if($v['pid'] != 0){
                $fg = '&emsp;&nbsp;'.'|';
                $v['icon'] = str_repeat($fg, $v['level']-1).'--';
            }
            if(!$upcheckbox && $v['class'] == '0'){
                $disabled = 'disabled';
            }
            if(in_array($v['id'],$fuids)){
                $checkeds = 'checked';
            }
            $arr[] = "<input type='checkbox' name='fid[]' title='{$v['icon']}{$v['title']}' value='{$v['id']}' lay-skin='primary' {$disabled} {$checkeds}>";
            $checkeds = $disabled = null;
        }
        return $arr;
    }
    //  获取样式
    public function temps($temps,$data = ''){
        $commonconf = require(CONF_PATH.'config.php'); //获取默认风格目录
        $gtempdir = $commonconf['template']['view_path'];
        $default = $plate = model('Config')->where('conf','temp')->value('conf_value');
        if($data !== 0 && $data !== null){
            //获取自订义风格目录
            if(is_dir($gtempdir.$data)){
                $plate = $data;
            }
        }
        if($default == 0 || $default == null){
            $default = 'default';
        }
        //  读取样式目录
        if(is_dir($gtempdir.$plate.'/labelpart')){
            $tempdir = $gtempdir.$plate.'/labelpart/';
        }else{
            $tempdir = $gtempdir.$default.'/labelpart/';
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
                if($v == '0'){
                    continue;
                }
                if($temps == $v){
                    $imgurl = "{$tempdir}{$v}.jpg";
                    $select .= "<option selected='selected' data-img='{$imgurl}' value='{$v}'>{$v}</option>";
                }else{
                    $select .= "<option data-img='{$tempdir}{$v}.jpg' value='{$v}'>{$v}</option>";
                }
            }
        }
        $temps = "<select name='listtqmp[temp]' lay-filter='listtqmp'>{$select}</select><div class='content mt-15'><img id='listtqmp-img' src='{$imgurl}' width='30%' height='auto' onerror=\"this.src='/cxadmin/images/imgnone.png'\"> </div>";
        return $temps;
    }
    public function labelarticle($data,$img){
        $map = '';
        if($data['status'] == 1){
            $map['status'] = 1;
        }
        if($data['jian'] == 1){
            $map['jian'] = 1;
        }
        if(!empty($data['mids']) && isset($data['mids']) && $data['mids'] !== 'all'){
            $map['mid'] = $data['mids'];
        }
        if(!empty($data['fids']) && isset($data['fids'])){
            $map['fid'] = ['in', $data['fids']];
        }
        if(!empty($data['fuids']) && isset($data['fuids'])){
            $map['aid'] = ['in', $data['fuids']];
        }
        if($img){
            $map['picurl'] = ['<>',''];
        }
        switch ($data['listorder']){
            case 0:
                $order = 'top desc,jian desc,sort desc,aid desc';
                break;
            case 1:
                $order = 'addtime desc';
                break;
            case 3:
                $order = 'hist desc';
                break;
            case 5:
                $order = 'seetime desc';
                break;
        }
        $data['contnum'];
        $labelarticle = model('Article')->where($map)->limit($data['num']-1,$data['rows'])->order($order)->select();
        $vals = '';
        $data['maxnum'] = $data['titlenum'];
        foreach ($labelarticle as $key => $val){
            $vals[] = model('Article')->readcont($val,$data);
        }
        return $vals;
    }
    //  获取栏目数据
    public function labelpart($data){
        $partmodel = new Part();
        $fuids = explode(',',$data['fids']);
        $allpart = $partmodel->allpart();
        foreach ($allpart as $k => $v){
            if($v['status'] == '0' || !in_array($v['id'],$fuids)){
                unset($allpart[$k]);
            }else{
                $allpart[$k]['url'] = url('Part/index',array('fid'=>$v['id']));
            }
        }
        return $allpart;
    }
}