<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class Diqu extends Model{


    //  解读地区
    public function uszip($data){
        $map = explode(',',$data);
        foreach ($map as $v){
            $list[] = $this->where('id',$v)->where('status',1)->value('title');
        }
        $list = implode('-',$list);
        return $list;
    }


}