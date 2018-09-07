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
use app\admin\model\Config as cxModel;
use app\common\controller\Upload;
use think\Loader;

class Config extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,models,lst,edit,so,userbase,wap,play,sms'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,lst,models,userbase,play,wap,sms');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Config');
            if(!$validate->scene('index')->check($data['webdb'])){
                $this->error($validate->getError());
            }
            $webdb = $this->shanxue($data['webdb']);
            if(empty($webdb['temp']['conf_value']) || !isset($webdb['temp']['conf_value'])){
                $webdb['temp']['conf_value'] = 'default';
            }
            $webdb['webkeywords'] = str_replace(array(' ','，'),',',$webdb['webkeywords']);
            if(!empty($webdb['weblogo']['conf_value']) || isset($webdb['weblogo']['conf_value'])){
                if($webdb['weblogo']['conf_value'] != $this->webdb['weblogo']){
                    if(!is_file($webdb['weblogo']['conf_value'])){
                        $this->error('文件不存在！');
                    }
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$this->webdb['weblogo']);
                    $upload = new Upload();
                    $webdb['weblogo']['conf_value'] = $upload->fileMove($webdb['weblogo']['conf_value'],'webdb');
                }
            }else{
                unlink(ROOT_PATH.$this->webdb['updir'].'/'.$this->webdb['weblogo']);
            }
            if($cxmodel->saveAll($webdb)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改网站配置成功！');
                $this->success('修改网站配置成功！');
            }else{
                $this->addlog('修改网站配置失败！');
                $this->error('修改网站配置失败！');
            }
        }
        $temps = $this->tempnum($this->webdb['temp']);
        $this->assign('temps',$temps);
        return view();
    }
    public function lst(){
        $sort = model('Config')->open();
        $this->assign('list',$sort);
        return view('list');
    }
    /*添加配置字段*/
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            if(cxModel::get($data['conf'])){
                $this->error('配置项已存在！');
            }
            $data = datatrim($data);
            $validate = Loader::validate('Config');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['conf_type'] == 1 || $data['conf_type'] == 2 ){
                $data['conf_desc'] = '';
                $data['conf_typelx'] = '3';
            }elseif($data['conf_type'] == 6){
                $data['conf_desc'] = '';
                $data['conf_typelx'] = intval($data['conf_typelx']);
            }else{
                $data['conf_desc'] = str_replace('，',',',$data['conf_desc']);
                $data['conf_typelx'] = '3';
            }
            if($add = $cxmodel->allowField(true)->save($data)){
                $this->addlog('添加【'.$data['conf_title'].'】配置项成功！');
                $this->success('添加【'.$data['conf_title'].'】配置项成功！','lst');
            }else{
                $this->addlog('添加【'.$data['conf_title'].'】配置项失败！');
                $this->error('添加配置项失败！');
            }
        }
        return view();
    }
    //  修改配置字段
    public function edit(){
        if(!request()->param('conf')){
            $this->error('访问地址错误！');
        }
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Config');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['conf_type'] == 1 || $data['conf_type'] == 2 ){
                $data['conf_desc'] = '';
                $data['conf_typelx'] = '3';
            }elseif($data['conf_type'] == 6){
                $data['conf_desc'] = '';
                $data['conf_typelx'] = intval($data['conf_typelx']);
            }else{
                $data['conf_desc'] = str_replace('，',',',$data['conf_desc']);
                $data['conf_typelx'] = '3';
            }
            if($add = $cxmodel->allowField(true)->save($data,$data['conf'])){
                $this->addlog('修改【'.$data['conf_title'].'】配置项成功！');
                webCacheRm('webdb');
                $this->success('修改【'.$data['conf_title'].'】配置项成功！','lst');
            }else{
                $this->addlog('修改【'.$data['conf_title'].'】配置项失败！');
                $this->error('修改配置项失败！');
            }
        }
        $edit = cxModel::get(input('conf'));
        $edit['disabled'] = 'readonly';
        $this->assign('postdb',$edit);
        return view('add');
    }
    //  删除配置字段
    public function del(){
        if(request()->isPost()){
            $data = input('post.');
            $del = cxModel::get($data['id']);
            if($del['nodel'] == 1){
                $this->error('此字段禁止删除！');
            }
            if($del['conf_type'] == 6){
                if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$del['conf_value'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$del['conf_value']);
                }
            }
            if(cxModel::destroy($data['id'])){
                $this->addlog('删除【'.$del['conf_title'].'】配置项成功！');
                webCacheRm('webdb');
                $this->success('删除【'.$del['conf_title'].'】配置项成功！');
            }else{
                $this->addlog('删除【'.$del['conf_title'].'】配置项失败！');
                $this->error('删除【'.$del['conf_title'].'】配置项失败！');
            }
        }
        $this->error('访问错误！');
    }
    //  搜索配置字段
    public function so(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $so['conf|conf_title|conf_value']=array('like',"%".$data['keyword']."%");
            $cxmodel = new cxModel();
            $solist = $cxmodel->where($so)->select();
            if(!$solist){
                $this->error('搜索结果不存在！');
            }
            $this->assign([
                'list' => $solist,
                'keyword' => $data['keyword'],
            ]);
            return view();
        }
        $this->error('访问错误！');
    }
    //  模型基本配置
    public function models(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改模型基本配置成功！');
                $this->success('修改模型基本配置成功！');
            }else{
                $this->addlog('修改模型基本配置失败！');
                $this->error('修改模型基本配置失败！');
            }
        }
        return view();
    }
    //  会员基本配置
    public function userbase(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改会员基本配置成功！');
                $this->success('修改会员基本配置成功！');
            }else{
                $this->addlog('修改会员基本配置失败！');
                $this->error('修改会员基本配置失败！');
            }
        }
        return view();
    }
    //  手机端基本配置
    public function wap(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改手机端基本配置成功！');
                $this->success('修改手机端基本配置成功！');
            }else{
                $this->addlog('修改手机端基本配置失败！');
                $this->error('修改手机端基本配置失败！');
            }
        }
        return view();
    }
    //  支付基本配置
    public function play(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改支付配置成功！');
                $this->success('修改支付配置成功！');
            }else{
                $this->addlog('修改支付配置失败！');
                $this->error('修改支付配置失败！');
            }
        }
        return view();
    }
    //  短信基本配置
    public function sms(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->addlog('修改短信配置成功！');
                $this->success('修改短信配置成功！');
            }else{
                $this->addlog('修改短信配置失败！');
                $this->error('修改短信配置失败！');
            }
        }
        return view();
    }
    //  外部提交返回
    public function webconf(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data = $this->shanxue($data['webdb']);
            $cxmodel = new cxModel();
            if($cxmodel->saveAll($data)){
                webCacheRm('webdb');    // 清除缓存
                $this->success('修改配置成功！');
            }else{
                $this->error('修改配置失败！');
            }
        }
        return view();
    }
    //  处理接收到的数据
    protected function shanxue($data){
        $webdb = [];
        foreach ($data as $k => $v){
            $webdb[$k] = array(
                'conf' => trim($k),
                'conf_value' => trim($v),
            );
        }
        return $webdb;
    }
}
