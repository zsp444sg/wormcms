<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-21
 * Time: 17:32
 */
namespace app\common\controller;

use think\Controller;

class Cxcache extends Controller{
    /*
     *  用户缓存
     */
    public function usercache($id){
        if(!$id){
            return false;
        }
        $usercache = cxbsmd5('user_'.$id);
        $options = [
            'type'   => 'File',
            'prefix'=>  $usercache,
            'path'  =>  CACHE_PATH,
            'expire' => 3600,
        ];
        return $options;
    }
    /*
     *  网站缓存
     */
    public function webcache(){
        $usercache = cxbsmd5('web');
        $options = [
            'type'   => 'File',
            'prefix'=>  $usercache,
            'path'  =>  CACHE_PATH,
            'expire' => 3600,
        ];
        return $options;
    }

}