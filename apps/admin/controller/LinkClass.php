<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-08-06
 * Time: 13:26
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\common\model\LinkClass as cxModel;
use think\Loader;

class LinkClass extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so,add,edit'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,status');
        $this->assign('btn',$btn);
    }
    //  分类首页
    public function index(){
        $cxmodel = new cxModel();
        $listdata = $cxmodel->order('sort desc,id asc')->paginate('20',false,['query' => request()->param()])->each(function ($item){
            $item->linknum = model('Link')->where('class',$item->id)->count();
        });
        $this->assign([
            'list' => $listdata,
        ]);
        return view();
    }
    //  添加分类
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('LinkClass');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($cxmodel->allowField(true)->isUpdate(false)->save($data)){
                webCacheRm('LinkClass');
                $this->addlog('添加链接分类【'.$data['title'].'】成功');
                $this->success('添加链接分类【'.$data['title'].'】成功','index');
            }else{
                $this->error('添加链接分类【'.$data['title'].'】失败');
            }
        }
        return view();
    }
    //  编辑分类
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $postdb = $cxmodel->where('id',request()->param('id'))->find();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('LinkClass');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if($cxmodel->allowField(true)->isUpdate(true)->save($data)){
                webCacheRm('LinkClass');
                $this->addlog('修改链接分类【'.$data['title'].'】成功');
                $this->success('修改链接分类【'.$data['title'].'】成功','index');
            }else{
                $this->error('修改链接分类【'.$data['title'].'】失败');
            }
        }
        $this->assign([
            'postdb' => $postdb,
        ]);
        return view('add');
    }
    //  更改显示状态
    public function see(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['status'] == 1){
                cxModel::update(['id' => $see['id'],'status' => '0']);
                webCacheRm('LinkClass');
                $this->addlog('隐藏链接分类【'.$see['title'].'】！');
                $this->success("隐藏链接分类【{$see['title']}】！");
            }else{
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('LinkClass');
                $this->addlog('显示链接分类【'.$see['title'].'】！');
                $this->success("显示链接分类【{$see['title']}】！");
            }
        }
    }
    //  删除分类
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()) {
            $deldata = cxModel::get(input('id'));
            $delnum = model('Link')->where('class',$deldata['id'])->count();
            if($delnum != 0){
                $this->error("本分类存在友情链接，请先删除！");
            }
            $title = $deldata['title'];
            if($deldata->delete()) {
                webCacheRm('LinkClass');
                $this->addlog("删除友情链接分类【{$title}】成功！");
                $this->success("删除友情链接分类【{$title}】成功！");
            }
        }
        return;
    }
    //  批量删除分类
    public function pdel(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data['pdel'] as $v){
                $deldata = cxModel::get($v);
                $delnum = model('Link')->where('class',$deldata['id'])->count();
                if($delnum != 0){
                    $this->error("本分类存在友情链接，请先删除！");
                }
                $title = $deldata['title'];
                if($deldata->delete()) {
                    $this->addlog("删除友情链接分类【{$title}】成功！");
                }
            }
            webCacheRm('LinkClass');
            $this->success("批量删除友情链接分类成功！");
        }
    }
    //  修改排序
    public function sort(){
        if(request()->isPost()) {
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
            webCacheRm('LinkClass');
            $this->addlog('更改友情链接分类排序成功！');
            $this->success('更改友情链接分类排序成功!');
        }
    }
}