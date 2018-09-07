<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use think\Model;

class Link extends Model{
    // 启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('Link/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('Link/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    //  获取全部友情链接
    public function alllink(){
        if(webCacheHas('Link')){
            $alllink = webCacheGet('Link');
        }else{
            $alllink = $this->where('status','1')->select();
            webCacheSet('Link',$alllink);
        }
        return $alllink;
    }
    //  返回显示的友情链接
    public function seelink(){
        $array = $this->alllink();
        if(empty($array) || !isset($array)){
            $array = '';
            return $array;
        }
        $class = \model('LinkClass')->linkclass();
        $class = array_column($class,'id');
        $imglink = $tlink = array();
        foreach ($array as $v){
            if(!empty($v['picurl']) && isset($v['picurl'])){
                $imglink[] = $v->toArray();
                continue;
            }
            if(in_array($v['class'],$class)){
                $tlink[$v['class']][] = $v->toArray();
            }
        }
        $seelink['imglink'] = $imglink;
        $seelink['tlink'] = $tlink;
        return $seelink;
    }
}