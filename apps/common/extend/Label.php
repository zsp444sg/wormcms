<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-01-21
 * Time: 11:34
 */
use app\common\extend\Cxforms;
unset($label);
$cxforms = new Cxforms();
$mobifile = $cxforms->file_read($temps);
//  获得模板所有标签
preg_match_all("/label\.[0-9a-zA-z\-\_]*/", $mobifile, $olds);//获得所有标签
unset($mobifile);
unset($cxforms);
if(empty($olds[0])){
    return true;
}
// 给所有标签赋值
//
$labelmodel = new \app\common\model\Label();
$labelmos['plate'] = $this->webdb['temp'];
$labelmos['mlabel'] = $parts['mlabel'];
$labelmos['parts']=0;
if(request()->param('parts')){
    $labelmos['parts'] = request()->param('parts');
}
if(!empty($parts) && !empty($parts['plate']) && $parts['plate'] && isset($parts['plate'])){
    $labelmos['plate'] = $parts['plate'];
}
$labelarr = $label = null;
$urllabel = request()->param();
if(request()->param('label') != 'show' || !request()->param('label')){
    if(webCacheHas('label_'.$labelmos['mlabel'].'_'.$labelmos['plate'].'_'.$labelmos['parts'])){
        $label = webCacheGet('label_'.$labelmos['mlabel'].'_'.$labelmos['plate'].'_'.$labelmos['parts']);
    }else{
        $labels = $labelmodel->contval($labelmos);
        if(!empty($labels)){
            foreach ($labels as $key => $val){
                if(request()->controller() == 'FuPart'){
                    $val['fuid'] = $parts['id'];
                }
                $label[$key] = controller('Label')->analysis($val,$key);
            }
            webCacheSet('label_'.$labelmos['mlabel'].'_'.$labelmos['plate'].'_'.$labelmos['parts'],$label);
        }
    }
}else{
    if(!session('_admin_') || session('userdb') !== session('_admin_')){
        $this->error("你没有此权限！");
    }
    webCacheRm('label_'.$labelmos['mlabel'].'_'.$labelmos['plate'].'_'.$labelmos['parts']);
    foreach ($olds[0] as $val){
        $val = explode('.',$val);
        $label_vals[] = $val[1];
    }
    unset($olds);
    $label_vals = arrayFlip($label_vals);
    //  读取标签
    $labels = $labelmodel->contval($labelmos);
    if(!empty($labels)){
        foreach ($labels as $key => $val){
            if(request()->controller() == 'FuPart'){
                $val['fuid'] = $parts['id'];
            }
            $label[$key] = controller('Label')->analysis($val,$key);
        }
    }
    foreach ($label_vals as $key => $val){
        if(isset($labels[$val])){
            if(empty($labels[$val])){
                $label[$val] =  "<div class='cx-label' data-type='editlabel' data-href='".url('admin/Label/index')."' data-title='{$labels[$val]['title']}' data-mlabel='{$labelmos['mlabel']}' data-typ='{$labels[$val]['type']}' data-plate='{$labelmos['plate']}'  data-parts='{$labelmos['parts']}' style='background: rgba(40,104,199,.7);border: 1px solid red;position: absolute;width: 50px;height: 30px;color: #fff;z-index: 9999;'></div><div class='label {$val}' id='{$val}'>无内容</div> ";
            }else{
                $label[$val] =  "<div class='cx-label' data-type='editlabel' data-href='".url('admin/Label/index')."' data-title='{$labels[$val]['title']}' data-mlabel='{$labelmos['mlabel']}' data-typ='{$labels[$val]['type']}' data-plate='{$labelmos['plate']}'  data-parts='{$labelmos['parts']}' style='background: rgba(40,104,199,.7);border: 1px solid red;position: absolute;width: 50px;height: 30px;color: #fff;z-index: 9999;'></div><div class='label {$val}' id='{$val}'>$label[$val]</div> ";
            }
        }else{
            $label[$val] = "<div class='cx-label' data-type='editlabel' data-href='".url('admin/Label/index')."' data-title='{$val}' data-typ='getnew' data-mlabel='{$labelmos['mlabel']}' data-plate='{$labelmos['plate']}'  data-parts='{$labelmos['parts']}' style='background: rgba(40,104,199,.7);border: 1px solid red;position: absolute;width: 50px;height: 30px;color: #fff;z-index: 9999;'></div><div class='label {$val}' id='{$val}'>新标签，无内容</div> ";
        }
    }
}
$this->assign('label',$label);

