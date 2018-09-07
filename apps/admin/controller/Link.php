<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-08-06
 * Time: 13:26
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\common\controller\Upload;
use app\common\model\Link as cxModel;
use think\Loader;

class Link extends Adminbase {
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
        $linkclass = model('LinkClass')->linkclass();
        if(empty($linkclass)){
            $this->error("请先添加友情链接分类",'LinkClass/index');
        }
        $map['class'] = $linkclass['0']['id'];
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        if(!empty($getdata['class'])){
            $map['class'] = $getdata['class'];
        }
        $listdata = $cxmodel->where($map)->order('sort desc,id asc')->paginate('30',false,['query' => request()->param()])->each(function ($item){
            $item->picurl = str_replace("http://www_cxbs_net/Ls_dir/","/{$this->webdb['updir']}/",$item->picurl);;
        });
        $this->assign([
            'linkclass' => $linkclass,
            'list' => $listdata,
        ]);
        return view();
    }
    //  添加友情链接
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Link');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['picurl'])){
                $upmodel = new Upload();
                $pirdir = "nav";
                $data['picurl'] = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data['picurl'],$pirdir);
                $data['picurl'] = $upmodel->editadd($data['picurl']);
            }
            if($cxmodel->allowField(true)->isUpdate(false)->save($data)){
                webCacheRm('Link');
                $this->addlog('添加链接分类【'.$data['title'].'】成功');
                $this->success('添加链接分类【'.$data['title'].'】成功',url('index',array('class'=>$data['class'])));
            }else{
                $this->error('添加链接分类【'.$data['title'].'】失败');
            }
        }
        $linkclass = model('LinkClass')->linkclass();
        $this->assign([
            'linkclass' => $linkclass,
        ]);
        return view();
    }
    //  编辑分类
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $upmodel = new Upload();
        $postdb = $cxmodel->where('id',request()->param('id'))->find();
        $postdb['picurl'] = $upmodel->editadd($postdb['picurl'],false);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Link');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['picurl']) && $data['picurl'] != $postdb['picurl']){
                $pirdir = "nav";
                $data['picurl'] = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data['picurl'],$pirdir);
                $data['picurl'] = $upmodel->editadd($data['picurl']);
                if(!empty($postdb['picurl'])){
                    @unlink(ROOT_PATH.$postdb['picurl']);
                }
            }
            if($cxmodel->allowField(true)->isUpdate(true)->save($data)){
                webCacheRm('Link');
                $this->addlog('修改友情链接【'.$data['title'].'】成功');
                $this->success('修改友情链接【'.$data['title'].'】成功',url('index',array('class'=>$data['class'])));
            }else{
                $this->error('修改友情链接【'.$data['title'].'】失败');
            }
        }
        $linkclass = model('LinkClass')->linkclass();
        $this->assign([
            'linkclass' => $linkclass,
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
                webCacheRm('Link');
                $this->addlog('隐藏友情链接【'.$see['title'].'】！');
                $this->success("隐藏友情链接【{$see['title']}】！");
            }else{
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('Link');
                $this->addlog('隐藏友情链接【'.$see['title'].'】！');
                $this->success("隐藏友情链接【{$see['title']}】！");
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
            $title = $deldata['title'];
            $upmodel = new Upload();
            $picurl = $upmodel->editadd($deldata['picurl'],false);
            if($deldata->delete()) {
                if(!empty($picurl)){
                    @unlink(ROOT_PATH.$picurl);
                }
                webCacheRm('Link');
                $this->addlog("删除友情链接【{$title}】成功！");
                $this->success("删除友情链接【{$title}】成功！");
            }
        }
        return;
    }
    //  批量删除分类
    public function pdel(){
        if(request()->isPost()){
            $upmodel = new Upload();
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data['pdel'] as $v){
                $deldata = cxModel::get($v);
                $title = $deldata['title'];
                $picurl = $upmodel->editadd($deldata['picurl'],false);
                if($deldata->delete()) {
                    if(!empty($picurl)){
                        @unlink(ROOT_PATH.$picurl);
                    }
                    $this->addlog("删除友情链接【{$title}】成功！");
                }
            }
            webCacheRm('LinkClass');
            $this->success("批量删除友情链接成功！");
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
            $this->addlog('更改友情链接排序成功！');
            $this->success('更改友情链接排序成功!');
        }
    }
}