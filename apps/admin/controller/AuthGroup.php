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
use app\admin\model\AuthGroup as cxModel;
use app\common\controller\Upload;
use think\Loader;
use think\Validate;

class AuthGroup extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,edit,so,groupauth'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,see,groupauth');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel->sorts($data);
            $this->success('更改排序成功！','index');
        }
        $list = $cxmodel->grouplst('0','1');
        foreach ($list as $k => $v){
            $v['usernum'] = \model('UserData')->where('group_id',$v['id'])->count();
            $allgroup[] = $v;
        }
        $this->assign('list',$allgroup);
        return view();
    }
    // 添加用户组
    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('AuthGroup');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $cxmodel = new cxModel();
            switch ($data['grouptype']){
                case 0:
                    $data['groupup'] = '0';
                    break;
                case 1:
                    $data['groupadmin'] = '0';
                    break;
            }
            if(!empty($data['groupicon']) && isset($data['groupicon'])){
                $upload = new Upload();
                $data['groupicon'] = $upload->fileMove($data['groupicon'],'group','copy',false);
            }
            $data = arrayNull($data);
            if($cxmodel->allowField(true)->isUpdate(false)->save($data)){
                $this->addlog('添加【'.$data['title'].'】用户组成功！');
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success('添加【'.$data['title'].'】用户组成功！','index');
            }else{
                $this->addlog('添加【'.$data['title'].'】用户组失败！');
                $this->error('添加用户组失败！');
            }
        }
        $sort = model('AuthRule')->sort('drop');
        $icon = $this->groupicon();
        $this->assign('pidauthrule',$sort);
        $this->assign('icon',$icon);
        return view();
    }
    // 修改用户组
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问地址错误！');
        }
        $cxmodel = new cxModel();
        $postdb = $cxmodel->where('id',trim(request()->param('id')))->find();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('AuthGroup');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            switch ($data['grouptype']){
                case 0:
                    $data['groupup'] = '0';
                    break;
                case 1:
                    $data['groupadmin'] = '0';
                    $data['rules'] = '';
                    break;
            }
            // 处理头像
            $upload = new Upload();
            if(!empty($data['groupicon']) && isset($data['groupicon']) && $data['groupicon'] != $postdb['groupicon']){
                $upload->fileDel($postdb['groupicon']);
                $data['groupicon'] = $upload->fileMove($data['groupicon'],'group','copy',false);
            }else{
                if(!empty($postdb['groupicon']) || isset($postdb['groupicon'])){
                    $upload->fileDel($postdb['groupicon']);
                }
            }
            switch ($data['id'] == 1){
                case 1:
                    $data['grouptype'] = '0';
                    $data['groupup'] = '0';
                    $data['groupadmin'] = '1';
                    $data['status'] = '1';
                    break;
            }
            $data = arrayNull($data);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                $this->addlog('修改【'.$data['title'].'】用户组成功！');
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success('修改【'.$data['title'].'】用户组成功！','index');
            }else{
                $this->addlog('修改【'.$data['title'].'】用户组失败！');
                $this->error( '修改【'.$data['title'].'】用户组失败！');
            }
        }
        $icon = $this->groupicon();
        $this->assign('icon',$icon);
        $this->assign('postdb',$postdb);
        return view('add');
    }
    //  更改状态
    public function see(){
        if(request()->isPost()){
            $id = input('cxbsid');
            if($id == 1){
                $this->error("此用户组不可禁用");
            }
            $cxmodel = new cxModel();
            $status = $cxmodel->where('id',$id)->find();
            if($status['status'] == '1'){
                $this->addlog("禁用【{$status['title']}】用户组！");
                cxModel::update(['id' => $id,'status' => '0']);
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success("禁用【{$status['title']}】用户组！");
            }else{
                $this->addlog("启用【{$status['title']}】用户组！");
                cxModel::update(['id' => $id,'status' => '1']);
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success("启用【{$status['title']}】用户组！");
            }
        }
        $this->error('非法操作！');
    }
    // 删除用户组
    public function del(){
        if(request()->isPost()){
            $delname = cxModel::get(input('id'));
            if(input('id') == 1){
                $this->error('超级管理员组禁止删除！');
            }
            if(input('id') == 5){
                $this->error('此用户组禁止删除！');
            }
            $delnum = model('UserData')->where('group_id',$delname['id'])->count();
            if($delnum !== 0){
                $this->error('用户组下存在用户，禁止删除！');
            }
            if($del = cxModel::destroy(input('id'))){
                if(is_file(ROOT_PATH.$delname['groupicon'])){
                    unlink(ROOT_PATH.$delname['groupicon']);
                }
                $this->addlog('删除【'.$delname['title'].'】用户组成功！');
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success('删除【'.$delname['title'].'】用户组成功！');
            }else{
                $this->addlog('删除【'.$delname['title'].'】用户组失败！');
                $this->error('删除用户组失败！');
            }
        }
        return redirect('index');
    }
    //  搜索
    public function so(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
        }else{
            $data = request()->param();
        }
        $data = datatrim($data);
        $so['title'] = array('like',"%".$data['keyword']."%");
        $list = $cxmodel->where($so)->order('sort desc,id asc')->paginate(20,false,['query' => request()->param()])->each(function($item, $key){
            $item->usernum = model('UserData')->where('group_id',$item['id'])->count();
        });
        if(!$list){
            $this->error('没有查询到你要的结果！');
        }
        $this->assign('list',$list);
        return view();
    }
    //  修改权限
    public function groupauth(){
        $cxmodel = new cxModel();
        $edit = $cxmodel->where('id',trim(request()->param('id')))->find();
        if(!$edit){
            $this->error('无此用户组!');
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data['rules'] as $key => $val){
                $check = Validate::is($val,'number');
                if (false === $check) {
                    $this->error('权限选择错误！');
                }
            }
            $data['rules'] = implode(',',$data['rules']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                $this->addlog("修改【{$edit['title']}】权限成功！");
                webCacheRm('group');
                userCacheRm($this->cxbsuser['uid'],'userauth');
                $this->success("修改【{$edit['title']}】权限成功！",'index');
            }else{
                $this->addlog("修改【{$edit['title']}】权限失败！");
                $this->error("修改【{$edit['title']}】权限失败！");
            }
        }
        $edit['rules'] = explode(',',$edit['rules']);
        $rule = model('AuthRule')->sort('group');
        $this->assign([
            'edit'=> $edit,
            'rulelist'=> $rule,
        ]);
        return view();
    }
    // 用户组头像
    protected function groupicon(){
        $dir = config('view_replace_str.__ADMIN__');
        $dir = explode('/',$dir);
        $dir = arrayNull($dir);
        $dir = implode('/',$dir);
        $dir = $dir."/groupicon/";
        if (!file_exists($dir)) {
            return false;
        }
        $xticon = glob($dir.'*.png');
        foreach ($xticon as $cx){
            $icon[] = array(
                'pic' => $cx,
                'picurl' => "/".$cx,
            );
        }
        return $icon;
    }
}
