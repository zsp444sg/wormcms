<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-08-07
 * Time: 15:33
 */
namespace app\common\model;
use think\Model;
class Search extends Model{
    public function solist(){
        if(webCacheHas('soartModel')){
            $somap = webCacheGet('soartModel');
            return $somap;
        }
        $artmodel = \model('ArtModel')->artmodel();
        if(empty($artmodel)){
            return true;
        }
        //  提取需要搜索的词
        $solist = null;
        foreach ($artmodel as $k => $v){
            $artconf = unserialize($v['config']);
            foreach ($artconf['field_db'] as $key => $val){
                if($val['formso'] == '1'){
                    $solist[$v['id']][] = $val['sqlname'];
                }
            }
        }
        if(empty($solist)){
            return true;
        }
        //  获取需要搜索的数据表
        foreach ($solist as $k => $v){
            $somap[$k]['table'] = 'article_content_'.$k;
            $somap[$k]['name'] = implode('|',$v);
        }
        unset($solist);
        unset($artconf);
        unset($artmodel);
        webCacheSet('soartModel',$somap);
        return $somap;
    }
}