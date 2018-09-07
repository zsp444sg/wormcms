<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;

use think\Model;

class AuthGroup extends Model{
    protected $type = [
        'status'    =>  'integer'
    ];
    // 所属会员组
    protected function getGrouptypezhAttr($value,$data){
        $grouptype = [ 0 => '系统会员组',1 => '普通会员组'];
        return $grouptype[$data['grouptype']];
    }
    // 启用状态
    protected function getStatuszanAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('AuthGroup/see').'" class="button button-S bg-red cx-click" data-type="sestatus">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('AuthGroup/see').'" class="button button-S bg-green cx-click" data-type="sestatus">启用</a>'];
        return $status[$data['status']];
    }
    // 后台权限
    protected function getGroupadminzhAttr($value,$data){
        $groupadmin = [ 0 => '<a href="#" class="button button-S bg-red">禁用</a>',1 => '<a href="'.url('groupauth',array('id' => $data['id'])).'" class="button button-S bg-green">修改</a>'];
        return $groupadmin[$data['groupadmin']];
    }
    public function sorts($data){
        foreach ($data as $k => $v){
            $this->update(['id'=> $k,'sort' => $v ]);
        }
        return;
    }
    public function allgroup(){
        if(webCacheHas('group')){
            $allgroup = webCacheGet('group');
        }else{
            $allgroup = $this->order('sort desc,id asc')->select();
            webCacheSet('group',$allgroup);
        }
        return $allgroup;
    }
    //  调用用户组显示列表
    public function grouplst($groupid = '0',$status='0'){
        $allgroup = $this->allgroup();
        if($status == '0'){
            foreach ($allgroup as $k => $v){
                if($v['status'] != '1'){
                    unset($allgroup[$k]);
                }
            }
        }
        return $allgroup;
    }

}