<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class ArtModel extends Model{

    //  查询所有模型信息并写入缓存
    public function artmodel(){
        if(webCacheHas('artmodel')){
            $artmodel = webCacheGet('artmodel');
        }else{
            $artmodel = $this->order('sort desc,id asc')->select();
            foreach ($artmodel as $k => $v){
                $artmodel[$k] = $v;
            }
            webCacheSet('artmodel',$artmodel);
        }
        return $artmodel;
    }
    //  获取所有可用模型
    public function seemodel(){
        $seemodel = $this->artmodel();
        if(empty($seemodel) || !isset($seemodel)){
            return true;
        }
        foreach ($seemodel as $k => $v){
            if($v['status'] == '0'){
                unset($seemodel[$k]);
            }
        }
        if(empty($seemodel) || !isset($seemodel)){
            return true;
        }
        return $seemodel;
    }

}