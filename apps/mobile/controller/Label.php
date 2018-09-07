<?php
namespace app\mobile\controller;


use app\common\controller\Mobilebase;
use app\common\controller\Upload;
use app\common\model\Label as cxModel;
use think\Cache;

class Label extends Mobilebase {

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
                if(!isset($valus['img']) || empty($valus['img'])){
                    return;
                }
                if(!isset($valus['url']) || empty($valus['url'])){
                    $valus['url'] = '#';
                }
                return "<a class='zoomImages' href='{$valus['url']}' style=\"background-image: url('/".$this->webdb['updir']."/".$valus['img']."')\"></a>";
                break;
            case 'partedit':
                $newtempcode = $htmlcode= $fhvalus = '';
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
