<?php
namespace app\home\controller;

use app\common\controller\Indexbase;
use app\common\controller\Upload;
use app\common\model\Label as cxModel;
use think\Cache;

class Label extends Indexbase {

    public function analysis($data,$title){
        if(!isset($data) || empty($data)){
            return;
        }
        if(!isset($data['conf']) || empty($data['conf'])){
            return;
        }
        $cxmodel = new cxModel();
        $valus = unserialize($data['conf']);
        switch ($data['type']){
            case 'htmledit':
                $valus = $this->sizedata($valus);
                $upmodel = new Upload();
                $valus = $upmodel->editadd($valus,false);
                return $valus;
                break;
            case 'imagesedit':
                $target = "target='_blank'";
                if(!isset($valus['img']) || empty($valus['img'])){
                    return;
                }
                if(!isset($valus['url']) || empty($valus['url'])){
                    $valus['url'] = '#';
                    $target = '';
                }
                if(isset($valus['class']) && $valus['class'] == '0'){
                    return "<a class='zoomImages' href='{$valus['url']}' style=\"background-image: url('/".$this->webdb['updir']."/".$valus['img']."')\"></a>";
                }else{
                    return "<a hrer='{$valus['url']}' {$target}><img class='img-responsive' src='/".$this->webdb['updir']."/".$valus['img']."' /></a>";
                }
                break;
            case 'parts':
                $newtempcode = $htmlcode= $fhvalus = '';
                //  获取所有标签变量
                preg_match_all("/(?<={)[^}]+/",$valus['tempcode'],$newlabel);
                $newlabel  = $newlabel[0];
                foreach($newlabel as $k => $v){
                    $v = str_replace("\$rs[","", $v);//获得所有标签;
                    $v1 = str_replace("]","",$v);//获得所有标签;
                    $array[] = str_replace("]","",$v1);//获得所有标签;
                }
                //  获取标签模板
                $newtempcode = str_replace("{\$","\$", $valus['tempcode']);//获得所有标签;
                $newtempcode = str_replace("]}","]",$newtempcode);//获得所有标签;
                $newtempcode = $this->sizedata($newtempcode);//获得所有标签;
                //  获取栏目数据
                $data = $cxmodel->labelpart($valus);
                if(empty($array) || !isset($array)){
                    return $fhvalus;
                }
                //  重新赋值
                foreach($array as $v){
                    foreach ($data as $key => $val){
                        if(empty($val[$v]) && !isset($val[$v])){
                            $val[$v] = '';
                        }
                        $labeldata[$key][$v] = $val[$v];
                    }
                }
                //  写入模板
                foreach ($labeldata as $k => $rs){
                    $htmlcode = addslashes($newtempcode);
                    eval("\$htmlcode=\"$htmlcode\";");
                    $fhvalus .= StripSlashes($htmlcode);
                }
                return $fhvalus;
                break;
            case 'partedit':
                $newtempcode = $htmlcode= $fhvalus = '';
                $imgs = false;
                //  获取所有标签变量
                preg_match_all("/(?<={)[^}]+/",$valus['tempcode'],$newlabel);
                $newlabel  = $newlabel[0];
                foreach($newlabel as $k => $v){
                    $v = str_replace("\$rs[","", $v);//获得所有标签;
                    $v1 = str_replace("]","",$v);//获得所有标签;
                    if($v1 == 'picurl1' || $v1 == 'picurl2' || $v1 == 'picurl3' || $v1 == 'picurl4' || $v1 == 'picurl'){
                        $imgs = true;
                    }
                    $array[] = str_replace("]","",$v1);//获得所有标签;
                }
                //  获取标签模板
                $newtempcode = str_replace("{\$","\$", $valus['tempcode']);//获得所有标签;
                $newtempcode = str_replace("]}","]",$newtempcode);//获得所有标签;
                $newtempcode = $this->sizedata($newtempcode);//获得所有标签;
                //  获取数据
                $data = $cxmodel->labelarticle($valus,$imgs);
                if(empty($array) || !isset($array) || !isset($data) || empty($data)){
                    return $fhvalus;
                }
                //  重新赋值
                foreach($array as $v){
                    foreach ($data as $key => $val){
                        if(empty($val[$v]) && !isset($val[$v])){
                            $val[$v] = '';
                        }
                        $labeldata[$key][$v] = $val[$v];
                    }
                }
                //  写入模板
                foreach ($labeldata as $k => $rs){
                    $htmlcode = addslashes($newtempcode);
                    eval("\$htmlcode=\"$htmlcode\";");
                    $fhvalus .= StripSlashes($htmlcode);
                }
                return $fhvalus;
                break;
            case 'fupartedit':
                $newtempcode = $htmlcode= $fhvalus = $valus['fuids'] = '';
                $fids = $valus['fids'];
                $fids = model('FuArticle')->distinct(true)->field('aid')->where('fuid','in',$fids)->paginate($valus['rows']);
                foreach ($fids as $k => $v){
                    $aids[] = $v['aid'];
                }
                if(!empty($aids) && isset($aids)){
                    $valus['fuids'] = implode(',',$aids);
                }
                $valus['fuid'] = $data['fuid'];
                unset($valus['fids']);
                if(empty($valus['fuids']) || !isset($valus['fuids'])){
                    return $fhvalus;
                }
                $data = $cxmodel->labelarticle($valus,$this->webdb['updir']);
                $newtempcode = str_replace("{\$","\$", $valus['tempcode']);//获得所有标签;
                $newtempcode = str_replace("]}","]",$newtempcode);//获得所有标签;
                $newtempcode = $this->sizedata($newtempcode);//获得所有标签;
                if(empty($data) || !isset($data)){
                    return $fhvalus;
                }
                foreach ($data as $k => $rs){
                    $htmlcode = addslashes($newtempcode);
                    eval("\$htmlcode=\"$htmlcode\";");
                    $fhvalus .= StripSlashes($htmlcode);
                }
                return $fhvalus;
                break;
            case 'diquedit':
                $map = explode(',',$valus['diqus']);
                foreach ($map as $val){
                    $dqs[] = model('Diqu')->where('id',$val)->where('status',1)->find();
                }
                $fhvalus = '';
                $newtempcode = preg_replace('/\$([\'a-zA-Z0-9\_]+)/',"label_array[\\1]",$valus['temps']);
                $newtempcode = str_replace("label_array","\$rs", $newtempcode);//获得所有标签;
                $newtempcode = $this->sizedata($newtempcode);//获得所有标签;
                if(empty($dqs) || !isset($dqs)){
                    return $fhvalus;
                }
                foreach ($dqs as $k => $rs){
                    $htmlcode = addslashes($newtempcode);
                    eval("\$htmlcode=\"$htmlcode\";");
                    $fhvalus .= StripSlashes($htmlcode);
                }
                return $fhvalus;
                break;
        }

    }

    protected function sizedata($data){
        $data = trim($data); //清除字符串两边的空格
        $data = htmlentities($data,ENT_SUBSTITUTE); //清除字符串两边的空格
        $data = preg_replace("/\n/","",$data);
        $data = preg_replace("/\r/","",$data);
        $data = str_replace(" &amp;nbsp; ","&nbsp;",$data);
        $data = str_replace("&nbsp;","",$data);
        $data = html_entity_decode($data, ENT_HTML5, "utf-8");
        return $data;
    }

}
