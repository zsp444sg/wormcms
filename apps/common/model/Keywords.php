<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class Keywords extends Model{

    //  获取使用最高前10个关键词
    public function artkeyword($num = '10'){
        $title = $this->order('num desc,id asc')->limit($num)->select();
        return $title;
    }
    //  写入关键词
    public function addkeyword($data,$uid = ''){
        $data = arrayFlip($data);
        foreach ($data as $v){
            $adt = $this->where('title',$v)->find();
            if($adt){
                $adt['num'] = $adt['num'] + 1;
                $adt->save();
            }else{
                $arr['uid'] = $uid;
                $arr['title'] = $v;
                $array[] = $arr;
            }
        }
        if(!empty($array)){
            $this->saveAll($array);
        }
        return true;
    }
}