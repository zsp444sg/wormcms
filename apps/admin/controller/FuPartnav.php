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
use app\common\model\FuPartnav as cxModel;
use think\Loader;

class FuPartnav extends Adminbase {

    public function index(){
        if(!request()->param('fid')){
            $this->error("请选择分类！",'FuPart/index');
        }
        $cxmodel = new cxModel();
        $list = $cxmodel->listsort('seelist',request()->param('fid'));
        $arr = '';
        if(is_array($list)){
            foreach ($list as $k => $v){
                if($v['pid'] != 0){
                    $fg = '|&nbsp;&nbsp;&nbsp;';
                    $v['icon'] = str_repeat($fg, $v['level']-1).'|---&nbsp;';
                }
                $arr[] = $v;
            }
        }
        $this->assign([
           'list' => $arr,
        ]);
        return view();
    }
    // 添加导航
    public function add(){
        if(!request()->param('fid')){
            $this->error("请选择分类！",'FuPart/index');
        }
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('FuPartnav');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($cxmodel->allowField(true)->save($data)){
                webCacheRm('funav');
                $this->addlog('添加辅栏目导航【'.$data['title'].'】成功');
                $this->success('添加辅栏目导航【'.$data['title'].'】成功',$this->redirect('index', ['fid' => $data['fid']]));
            }else{
                $this->error('添加辅栏目导航【'.$data['title'].'】失败');
            }
        }
        $postdb['pids'] = $cxmodel->listsort('list',request()->param('fid'));
        $this->assign('postdb',$postdb);
        return view();
    }
    // 修改导航
    public function edit(){
        if(!request()->param('fid') || !request()->param('id')){
            $this->error("请选择分类！",'FuPart/index');
        }
        $cxmodel = new cxModel();
        $postdb = $cxmodel->where('id',request()->param('id'))->find();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('FuPartnav');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                webCacheRm('funav');
                $this->addlog('修改辅栏目导航【'.$data['title'].'】成功');
                $this->success('修改辅栏目导航【'.$data['title'].'】成功',$this->redirect('index', ['fid' => $data['fid']]));
            }else{
                $this->error('修改辅栏目导航【'.$data['title'].'】失败');
            }
        }
        $postdb['pids'] = $cxmodel->listsort('uplist',$postdb['fid'],$postdb['id'],$postdb['pid']);
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
            webCacheRm('funav');
            $this->addlog('更改辅助导航排序成功！');
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
            $deldata = cxModel::get(input('id'));
            $title = $deldata['title'];
            $dondata = $cxmodel->deldata($deldata['id']);//获取下级导航
            if($deldata->delete()){
                foreach ($dondata as $v){
                    $dels = cxModel::get($v);
                    $dtitle = $dels['title'];
                    $dels->delete();
                    $this->addlog('删除辅助导航【'.$dtitle.'】成功');
                }
                webCacheRm('funav');
                $this->addlog("删除辅助导航【{$title}】成功！");
                $this->success("删除辅助导航【{$title}】成功！");
            }else{
                $this->error("删除辅助导航【{$title}】失败！");
            }
        }
        return;
    }
}
