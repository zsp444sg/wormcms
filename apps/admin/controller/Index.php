<?php
// +----------------------------------------------------------------------
// | 火凤凰CMS内容管理系统
// +----------------------------------------------------------------------
// | Copyright (c) 2015~2018 http://cxbs.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 赵志广 <amdin@cxbs.net>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\controller\Adminbase;

class Index extends Adminbase {
    public function index(){
        $menu = $this->adminmenu($this->cxbsuser['uid']);
        foreach ($menu as $key => $val){
            if(session('_admin_.open') == '0' && $val['menusee'] != '1'){
                unset($menu[$key]);
            }
        }
        $cont['model'] = $this->models();
        $menu = model('AuthRule')->_grouplist($menu);
        $this->assign([
           'menu' => $menu,
            'cont' => $cont,
        ]);
        return view();
    }
    //  获取模型信息
    public function models(){
        $data = model('ArtModel')->order('sort desc,id asc')->select();
        $array[] = array(
            "mid" => 0,
            "title" => "文章",
            "icon" => "/cxadmin/images/folder.png",
        );
        foreach ($data as $k => $v){
            $arr['mid'] = $v['id'];
            if(!empty($v['futitle'])){
                $arr['title'] = $v['futitle'];
            }else{
                $arr['title'] = $v['title'];
            }
            if(!empty($v['logo']) && $v['logo']){
                $arr['icon'] = $v['logo'];
            }else{
                $arr['icon'] = "/cxadmin/images/folder.png";
            }
            $array[] = $arr;
        }
        return $array;
    }
}
