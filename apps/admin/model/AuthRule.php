<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;

use think\Model;

class AuthRule extends Model{
    protected $type = [
        'status'    =>  'integer'
    ];
    //  修改权限启用状态
    protected function getStatuszhAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['id'].'" data-href="'.url('AuthRule/see').'" data-type="sestatus" class="button button-S bg-red cx-click">禁用</a>',1 => '<a data-id="'.$data['id'].'" data-href="'.url('AuthRule/see').'" data-type="sestatus" class="button button-S bg-green cx-click">启用</a>'];
        return $status[$data['status']];
    }
    //  获取所有权限信息并写入缓存
    public function allauth(){
        if(webCacheHas('auth')){
            $allauth = webCacheGet('auth');
        }else{
            $map = '';
            if(session('_admin_.open') == '0'){
                $map['open'] = '1';
                $map['status'] = '1';
            }
            $allauth = $this->where($map)->order('sort desc,id asc')->select();
            webCacheSet('auth',$allauth);
        }
        return $allauth;
    }
    /*
     *  权限调用
     */
    public function sort($name,$id=''){
        $data = $this->allauth();
        switch ($name){
            case 'list':
                return $this->_sort($data);
                break;
            case 'drop':
                return $this->drop($data);
                break;
            case 'down':
                return $this->down($data,$id);
                break;
            case 'group':
                return $this->_grouplist($data);
                break;
        }

    }
    /*权限列表页调用*/
    public function _sort($data,$pid = 0){
        static $array = array();
        foreach ($data as $key => $val){
            if($val['pid'] == $pid){
                $array[] = $val;
                $this->_sort($data,$val['id']);
            }
        }
        return $array;
    }
    /*下拉菜单调用*/
    public function drop($data,$pid = 0){
        static $array = array();
        foreach ($data as $key => $val){
            if($val['pid'] == $pid){
                $val['dtitle'] = '';
                if($val['pid'] != 0){
                    $val['dtitle'] = '|'.str_repeat('---',$val['level']-1);
                }
                $array[] = $val;
                if($val['pid'] == 0){
                    $this->drop($data,$val['id']);
                }
            }
        }
        return $array;
    }
    //  查询指定子栏目
    public function down($data,$id){
        static $arr = array();
        foreach ($data as $k => $val){
            if($val['pid'] == $id){
                $arr[] = $val;
                $this->down($data,$val['id']);
            }
        }
        return $arr;
    }
    //  修改权限调用上级栏目--去除子栏目
    public function editpid($data,$ipid){
        $arr = $this->down($data,$ipid);
        foreach ($data as $k => $v){
            if($ipid == $v['id']){
                unset($data[$k]);
            }
            foreach ($arr as $val){
                if($val['id'] == $v['id']){
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }
    /*
     *  权限排序打包
     */
    public function _grouplist($data,$pid = 0){
        $arr=array();
        foreach ($data as $key => $rs){
            if($rs['pid'] == $pid){
                $rs['zauth'] = $this->_grouplist($data,$rs['id']);
                $arr[] = $rs;
            }
        }
        return $arr;
    }
    //修改排序
    public function sorts($data){
        foreach ($data as $k => $v){
            $this->update(['id'=> $k,'sort' => $v ]);
        }
        return;
    }



}