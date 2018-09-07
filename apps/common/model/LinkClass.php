<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use think\Model;

class LinkClass extends Model{
    // 启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('LinkClass/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('LinkClass/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    //  获取所有链接分类并缓存
    public function alllinkclass(){
        if(webCacheHas('LinkClass')){
            $alllinkclass = webCacheGet('LinkClass');
        }else{
            $alllinkclass = $this->order('sort desc,id asc')->select();
            webCacheSet('LinkClass',$alllinkclass);
        }
        return $alllinkclass;
    }
    //  获取启用的友情链接分类
    public function linkclass(){
        $array = $this->alllinkclass();
        if(empty($array) || !isset($array)){
            $array = '';
            return $array;
        }
        foreach ($array as $v){
            if($v['status'] != '1'){
                continue;
            }
            $linkclass[] = $v->toArray();
        }
        return $linkclass;
    }
}