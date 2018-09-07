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
use app\admin\model\Nav as cxModel;
use app\common\controller\Upload;
use think\Loader;

class Nav extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,edit'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }
    public function index($seetype = 0){
        $seetype = intval($seetype);
        $cxmodel = new cxModel();
        $list = $cxmodel->listsort('seelist');
        $arr = '';
        foreach ($list as $k => $v){
            if($v['seetype'] == null){
                $v['seetype'] = 0;
            }
            if($v['status'] == '2'){
                if($seetype != '4'){
                    continue;
                }
            }else{
                if($seetype == '4'){
                    continue;
                }
            }
            if($seetype == '1'){
                if($v['pid'] == '0' && $v['seetype'] != '1' && $v['seetype'] != '0'){
                    continue;
                }
            }elseif($seetype == '2'){
                if($v['pid'] == '0' && $v['seetype'] != '2' && $v['seetype'] != '0'){
                    continue;
                }
            }elseif($seetype == '3'){
                if($v['pid'] == '0' && $v['seetype'] != '3'){
                    continue;
                }
            }
            if($v['pid'] != 0){
                $fg = '|&nbsp;&nbsp;&nbsp;';
                $v['fg'] = str_repeat($fg, $v['level']-1).'|---&nbsp;';
            }
            $arr[] = $v;
        }
        if(!empty($arr) && isset($arr) && is_array($arr)){
            $arr = $cxmodel->_zcolumn($arr);
        }
        $this->assign([
           'list' => $arr,
        ]);
        return view();
    }
    // 添加导航
    public function add($pid = '0'){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        if(!empty($getdata['pid'])){
            $pid = $getdata['pid'];
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Nav');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['icon']['class'] == '1'){
                $upmodel = new Upload();
                $pirdir = "nav";
                $icon = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data['icon']['icon'],$pirdir);
                $data['icon']['icon'] = $upmodel->editadd($icon);
            }
            $data['icon'] = serialize($data['icon']);
            if($cxmodel->allowField(true)->save($data)){
                webCacheRm('nav');
                $this->addlog('添加导航【'.$data['title'].'】成功');
                $this->success('添加导航【'.$data['title'].'】成功','index');
            }else{
                $this->error('添加导航【'.$data['title'].'】失败');
            }
        }
        $postdb['pids'] = $cxmodel->listsort('list',$id=null,$pid);
        $this->assign('postdb',$postdb);
        return view();
    }
    // 修改导航
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $upmodel = new Upload();
        $postdb = $cxmodel->where('id',request()->param('id'))->find();
        $postdb['icon'] = unserialize($postdb['icon']);
        $icons = array();
        if(!empty($postdb['icon']['class']) && $postdb['icon']['class'] == '1'){
            $icons = $postdb['icon'];
            $icons['icon'] = $upmodel->editadd($icons['icon'],false);
            $postdb['icon'] = $icons;
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Nav');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['icon']['class'] == '1'){
                $upmodel = new Upload();
                if($data['icon']['icon'] != $postdb['icon']['icon']){
                    $pirdir = "nav";
                    $icon = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data['icon']['icon'],$pirdir);
                    $data['icon']['icon'] = $upmodel->editadd($icon);
                    if(!empty($icons['icon'])){
                        @unlink(ROOT_PATH.$postdb['icon']['icon']);
                    }
                }
            }
            $data['icon'] = serialize($data['icon']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                webCacheRm('nav');
                $this->addlog('修改导航【'.$data['title'].'】成功');
                $this->success('修改导航【'.$data['title'].'】成功','index');
            }else{
                $this->error('添加导航【'.$data['title'].'】失败');
            }
        }
        $postdb['pids'] = $cxmodel->listsort('uplist',$postdb['id'],$postdb['pid']);
        $this->assign('postdb',$postdb);
        return view('add');
    }
    //  排序
    public function sort(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data['sort'] as $k => $v){
                $list[] = array(
                    'id' => $k,
                    'sort' => $v
                );
            }
            $cxmodel = new cxModel();
            $cxmodel->saveAll($list);
            webCacheRm('nav');
            $this->addlog('更改导航排序成功！');
            $this->success('更改排序成功!');
        }
        $this->error('访问错误！');
    }
    //  删除
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $upmodel = new Upload();
            $deldata = cxModel::get(input('id'));
            $title = $deldata['title'];
            $icons = unserialize($deldata['icon']);
            $dondata = $cxmodel->listsort('del',$deldata['id']);//获取下级导航
            if($deldata['status'] != '2'){
                $cxmodel->save(['status'=>'2'],['id'=>$deldata['id']]);
                foreach ($dondata as $k => $v){
                    $list[] = array(
                        'id' => $v['id'],
                        'status' => '2'
                    );
                }
                if(!empty($list) && isset($list)){
                    $cxmodel->saveAll($list);
                }
                webCacheRm('nav');
                $this->success('已删除至回收站！');
            }
            if($deldata->delete()){
                if($icons['class'] == '1'){
                    if(!empty($icons['icon'])){
                        $icons['icon'] = $upmodel->editadd($icons['icon'],false);
                        @unlink(ROOT_PATH.$icons['icon']);
                    }
                    unset($icons);
                }
                if(!empty($dondata)){
                    foreach ($dondata as $v){
                        $dels = cxModel::get($v);
                        $dtitle = $dels['title'];
                        $icons = unserialize($dels['icon']);
                        if($icons['class'] == '1'){
                            if(!empty($icons['icon'])){
                                $icons['icon'] = $upmodel->editadd($icons['icon'],false);
                                @unlink(ROOT_PATH.$icons['icon']);
                            }
                            unset($icons);
                        }
                        $dels->delete();
                        $this->addlog('删除导航【'.$dtitle.'】成功');
                    }
                }
                webCacheRm('nav');
                $this->addlog("删除导航【{$title}】成功！");
                $this->success("删除导航【{$title}】成功！");
            }else{
                $this->error("删除导航【{$title}】失败！");
            }
        }
        return;
    }
    //  修改显示状态
    public function see(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['status'] == 1){
                cxModel::update(['id' => $see['id'],'status' => '0']);
                webCacheRm('nav');
                $this->addlog('隐藏导航【'.$see['title'].'】！');
                $this->success("隐藏导航【{$see['title']}】！");
            }elseif($see['status'] == 0){
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('nav');
                $this->addlog('显示导航【'.$see['title'].'】！');
                $this->success("显示导航【{$see['title']}】！");
            }else{
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('nav');
                $this->addlog('还原导航【'.$see['title'].'】！');
                $this->success("还原导航【{$see['title']}】！");
            }
        }
        $this->error('访问错误！');
    }
    //  批量删除
    public function pdel(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new cxModel();
            $upmodel = new Upload();
            $data = implode(',',$data['pdel']);
            $data = $cxmodel->all($data);
            foreach ($data as $key => $val){
                $deldondata = null;
                $deldondata = $cxmodel->listsort('del',$val['id']);
                if($val['status'] != '2'){
                    $cxmodel->where('id',$val['id'])->update(['status'=>'2']);
                    if(!empty($deldondata) && isset($deldondata)){
                        foreach ($deldondata as $k => $v){
                            $list[] = array(
                                'id' => $v['id'],
                                'status' => '2'
                            );
                        }
                        $cxmodel->saveAll($list);
                    }
                    continue;
                }else{
                    $icons =  $cxmodel->where('id',$val['id'])->value('icon');
                    $icons = unserialize($icons);
                    if($icons['class'] == '1'){
                        if(!empty($icons['icon'])){
                            $icons['icon'] = $upmodel->editadd($icons['icon'],false);
                            @unlink(ROOT_PATH.$icons['icon']);
                        }
                        unset($icons);
                    }
                    $cxmodel->where('id',$val['id'])->delete();
                    if(!empty($deldondata) && isset($deldondata)){
                        foreach ($deldondata as $k => $v){
                            $cxmodel->where('id',$v['id'])->delete();
                        }
                    }
                }
            }
            webCacheRm('nav');
            $this->addlog('批量删除导航成功！');
            $this->success('批量删除导航成功!');
        }
        $this->error("访问错误！");
    }
    //  批量还原
    public function phuan(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new cxModel();
            $data = implode(',',$data['pdel']);
            $data = $cxmodel->all($data);
            foreach ($data as $key => $val){
                $cxmodel->where('id',$val['id'])->update(['status'=>'1']);
            }
            webCacheRm('nav');
            $this->addlog('批量还原导航成功！');
            $this->success('批量还原导航成功!');
        }
    }
}
