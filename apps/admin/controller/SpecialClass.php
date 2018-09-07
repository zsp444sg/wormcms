<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\common\model\SpecialClass as cxModel;
use think\Loader;

class SpecialClass extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        $list = $cxmodel->order('sort desc,id asc')->select();
        $list = $cxmodel->_zcolumn($list);
        $arr = '';
        foreach ($list as $k => $v){
            $v['contnum'] = model('Special')->where('class',$v['id'])->count();
            if($v['pid'] != 0){
                $fg = '&emsp;&nbsp;&emsp;'.'|';
                $v['icon'] = str_repeat($fg, $v['level']-1).'--';
            }
            $arr[] = $v;
        }
        $this->assign('list',$arr);
        return view();
    }
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->datatrim($data);
            $validate = Loader::validate('SpecialClass');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['plate'] == 0){
                $data['plate'] = null;
            }
            $data['keywords'] = str_replace(array(' ','，'),',',$data['keywords']);
            $data['template'] = serialize($data['temp']);
            if(model('SpecialClass')->allowField(true)->save($data)){
                $this->addlog('添加专题分类【'.$data['title'].'】成功');
                $this->success('添加专题分类【'.$data['title'].'】成功','index');
            }else{
                $this->error('添加专题分类【'.$data['title'].'】失败');
            }
        }
        $postdb['pids'] = $cxmodel->pids();
        $postdb['plate'] = $this->catalog();
        $this->assign('postdb',$postdb);
        return view();
    }
    public function edit(){
        if(!request()->param()){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->datatrim($data);
            $validate = Loader::validate('SpecialClass');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if($data['plate'] == 0){
                $data['plate'] = null;
            }
            $data['keywords'] = str_replace(array(' ','，'),',',$data['keywords']);
            $data['template'] = serialize($data['temp']);
            if(model('SpecialClass')->allowField(true)->save($data,$data['id'])){
                $this->addlog('修改专题分类【'.$data['title'].'】成功');
                $this->success('修改专题分类【'.$data['title'].'】成功','index');
            }else{
                $this->error('修改专题分类【'.$data['title'].'】失败');
            }
        }
        $edit = cxModel::get(input('id'));
        $edit['pids'] = $cxmodel->deldonlist($edit['id'],$edit['pid']);
        $edit['plate'] = $this->catalog($edit);
        $this->assign('postdb',$edit);
        return view('add');
    }
    //  删除
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $deldata = cxModel::get(input('id'));
            $id = $deldata['id'];
            $title = $deldata['title'];
            if(!$cxmodel->delarticle($deldata['id'])){
                $this->error('分类存在专题，请处理后再行删除！');
            }
            $dondata = $cxmodel->donlevel($deldata['id']);//获取下级栏目
            if(!empty($dondata)){
                foreach ($dondata as $v){
                    if(!$cxmodel->delarticle($v['id'])){
                        $this->error('下级分类存在专题，请处理后再行删除！');
                    }
                }
            }
            if($deldata->delete()){
                foreach ($dondata as $v){
                    $dels = cxModel::get($v);
                    $dtitle = $dels['title'];
                    $dels->delete();
                    $this->addlog('删除专题分类【'.$dtitle.'】成功');
                }
                $this->addlog("删除专题分类【{$title}】成功！");
                $this->success("删除专题分类【{$title}】成功！");
            }else{
                $this->error("删除专题分类【{$title}】失败！");
            }
        }
        return;
    }

    public function pdel(){
        if(request()->isPost()){
            $deldata = input('post.');
            $deldata = $this->datatrim($deldata['delid']);
            $cxmodel = new cxModel();
            foreach ($deldata as $k => $v){
                $dondata = $cxmodel->donlevel($v); // 查询下级分类
                if(!$cxmodel->delarticle($v)){
                    $this->error('分类存在专题，请处理后再行删除！');
                }
                if(!empty($dondata)){
                    foreach ($dondata as $val){
                        if(!$cxmodel->delarticle($val['id'])){
                            $this->error('下级分类存在专题，请处理后再行删除！');
                        }
                    }
                }
            }
            if(!empty($dondata)){
                foreach ($dondata as $v){
                    $arr[] = $v['id'];
                }
                $deldata = array_merge($deldata,$arr);
                $deldata = array_unique($deldata);
            }
            foreach ($deldata as $k => $v){
                $finds = cxModel::get($v);
                $title = $finds['title'];
                $finds->delete();
                $this->addlog("删除专题分类【{$title}】成功！");
            }
            $this->success('批量删除成功!');
        }
        $this->error('访问错误！');
    }
    //  排序
    public function sort(){
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->datatrim($data);
            foreach ($data['sort'] as $k => $v){
                $list[] = array(
                    'id' => $k,
                    'sort' => $v
                );
            }
            $cxmodel = new cxModel();
            $cxmodel->saveAll($list);
            $this->addlog('更改专题分类排序成功！');
            $this->success('更改排序成功!');
        }
        $this->error('访问错误！');
    }
    public function so(){
        if(!request()->param()){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->datatrim($data);
            $so['title']=array('like',"%".$data['keyword']."%");
            $cxmodel = new cxModel();
            $list =  $cxmodel->where($so)->order('sort desc,id asc')->select();
            foreach ($list as $k => $v){
                $v['contnum'] = model('Special')->count();
            }
            if(!$list){
                $this->error('没有查询到你要的结果！');
            }
            $this->assign('list',$list);
            return view();
        }
        return;
    }
}