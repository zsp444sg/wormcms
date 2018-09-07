<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;
class Source extends Model{

    //  获取使用最高前10个来源
    public function artkeyword($num = '10'){
        $title = $this->order('num desc,id asc')->limit($num)->select();
        return $title;
    }
    //  写入来源
    public function addsource($data,$uid = ''){
        $adt = $this->where('url','like','%'.$data['url'].'%')->find();
        if($adt){
            $adt['num'] = $adt['num'] + 1;
            $adt->save();
        }else{
            $arr['uid'] = $uid;
            $arr['title'] = $data['title'];
            $arr['url'] = $data['url'];
            $this->save($arr);
        }
        return true;
    }

}