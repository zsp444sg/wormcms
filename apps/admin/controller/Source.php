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
use app\common\model\Source as cxModel;
use think\Loader;

class Source extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,edit,so,add'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }

    public function index(){
        $cxmodel = new cxModel();
        $list = $cxmodel->order('num desc,id desc')->paginate(20);
        $this->assign('list',$list);
        return view();
    }
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Source');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['uid'] = $this->cxbsuser['uid'];
            if($cxmodel->allowField(true)->save($data)){
                $this->addlog('添加【'.$data['title'].'】来源成功');
                $this->success('添加【'.$data['title'].'】来源成功','index');
            }else{
                $this->error('添加【'.$data['title'].'】来源失败');
            }
        }
        return view();
    }
    public function edit(){
        if(!request()->param()){
            $this->error('访问错误！');
        }
        $getdata = request()->param();
        $cxmodel = new cxModel();
        $edit = $cxmodel->where('id',$getdata['id'])->find();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Source');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if($cxmodel->allowField(true)->save($data,$data['id'])){
                $this->addlog('修改【'.$data['title'].'】来源成功');
                $this->success('修改【'.$data['title'].'】来源成功','index');
            }else{
                $this->error('修改【'.$data['title'].'】来源失败');
            }
        }
        $this->assign('postdb',$edit);
        return view('add');
    }
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $getdata = request()->param();
        $cxmodel = new cxModel();
        $deldata = $cxmodel->where('id',$getdata['id'])->find();
        $title = $deldata['title'];
        if($deldata->delete()){
            $this->addlog('删除【'.$title.'】来源成功');
            $this->success('删除【'.$title.'】来源成功','index');
        }else{
            $this->error('删除【'.$title.'】来源失败');
        }
        return view();
    }
    public function pdel(){
        if(request()->isPost()){
            $deldata = input('post.');
            $deldata = datatrim($deldata['delid']);
            $cxmodel = new cxModel();
            foreach ($deldata as $k => $v){
                $finds =$cxmodel->where('id',$v)->find();
                $title = $finds['title'];
                $finds->delete();
                $this->addlog("删除来源【{$title}】成功！");
            }
            $this->success('批量删除成功!');
        }
        $this->error('访问错误！');
    }
    public function so(){
        if(!request()->param()){
            $this->error('访问错误！');
        }
        $data = request()->param();
        $data = datatrim($data);
        $cxmodel = new cxModel();
        $so['title|url']=array('like',"%".$data['keyword']."%");
        $list =  $cxmodel->where($so)->order('num desc,id desc')->paginate('20',false,['query' => request()->param()]);
        if(!$list){
            $this->error('没有查询到你要的结果！');
        }
        $this->assign('list',$list);
        return view('index');
    }
}
