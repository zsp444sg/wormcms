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
use app\admin\model\ArtModel as cxModel;
use app\common\extend\Cxforms;
use think\Db;
use think\Loader;

class ArtModel extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so,add,edit,fieldlist,add_field,edit_field'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,add_field,fieldlist,edit_field');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        $list = $cxmodel->artmodel();
        foreach ($list as $v){
            $v['partnum'] = model('Part')->where('mid',$v['id'])->count();
            $v['articlenum'] = model('Article')->where('mid',$v['id'])->count();
        }
        $this->assign('list',$list);
        return view();
    }
    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $cxmodel = new cxModel();
            $cid = $cxmodel->order('id desc')->find();
            if($cid){
                $data['id'] = $cid['id'] + 1;
            }else{
                $data['id'] = 100;
            }
            $data = datatrim($data);
            $validate = Loader::validate('ArtModel');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['config'] = $cxmodel->zipmodel($data); //  打包基本信息
            if($cxmodel->allowField(true)->save($data)){
                $cxmodel->addtable($data);
                webCacheRm('artmodel');
                $this->addlog('添加【'.$data['title'].'】模型成功');
                $this->success('添加【'.$data['title'].'】模型成功','index');
            }else{
                $this->error('添加【'.$data['title'].'】模型失败');
            }
        }
        return view();
    }
    //  修改模型基本信息
    public function edit($id){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $edits = cxModel::get($id);
        $model = unserialize($edits['config']);
        $edit = $model['model_db'];
        $edit['id'] = $edits['id'];
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('ArtModel');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $cxmodel = new cxModel();
            $data = datatrim($data); // 处理数据
            $data['config'] = $cxmodel->zipmodel($data); //  打包基本信息
            if($cxmodel->allowField(true)->save($data,$data['id'])){
                webCacheRm('artmodel');
                $this->addlog('修改【'.$data['title'].'】模型成功');
                $this->success('修改【'.$data['title'].'】模型成功','index');
            }else{
                $this->error('修改【'.$data['title'].'】模型失败');
            }
        }
        $this->assign('postdb',$edit);
        return view('add');
    }
    //  模型排序
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
            model('ArtModel')->saveAll($list);
            $this->addlog('更改模型排序成功！');
            $this->success('更改排序成功!');
        }
        $this->error('访问错误！');
    }
    //  模型状态
    public function see(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['status'] == 1){
                cxModel::update(['id' => $see['id'],'status' => '0']);
                webCacheRm('artmodel');
                $this->addlog('禁用【'.$see['title'].'】模型！');
                $this->success("禁用【{$see['title']}】模型！");
            }else{
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('artmodel');
                $this->addlog('启用【'.$see['title'].'】模型！');
                $this->success("启用【{$see['title']}】模型！");
            }
        }
        $this->error('访问错误！');
    }
    //  删除模型
    public function del(){
        if(!request()->param()){
            $this->error("没有选择要删除的模型！");
        }
        if(request()->isPost()){
            $data = input('post.');
            //  获取栏目信息
            $cxmodel = new cxModel();
            $deldata = cxModel::get($data['id']);
            $title = $deldata['title'];
            //  查询是否有栏目存在
            $partnum = model('Part')->where('mid',$deldata['id'])->count();
            if($partnum > 0){
                $this->error("模型存在栏目，请处理后再行删除！");
            }
            $artpartnum = model('Article')->where('mid',$deldata['id'])->count();
            if($artpartnum > 0){
                $this->error("模型存在内容，请处理后再行删除！");
            }
            if(is_file(ROOT_PATH."data/post_temp/post_{$deldata['id']}.htm")){
                @unlink(ROOT_PATH."data/post_temp/post_{$deldata['id']}.htm");
            }
            webCacheRm('artmodel');
            $cxmodel->delfield($deldata);
            $deldata->delete();
            $this->addlog("删除模型【{$title}】成功！");
            $this->success("删除模型【{$title}】成功！");

        }
        $this->error('访问错误！');
    }
    //  模型字段列表
    public function fieldlist($id){
        if(!request()->param('id') || !cxModel::get(input('id'))){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $edit = cxModel::get(input('id'));
        $config = unserialize($edit['config']);
        $fielddb = '';
        if(isset($config['field_db'])){
            $cxforms = new Cxforms();
            $fielddb = $cxforms->formtext($config['field_db']);
        }
        if(request()->isPost()){
            $data = input('post.');
            foreach ($data as $k => $v){
                $fielddb[$k]['sort'] = $v;
            }
            $config['field_db'] = $cxmodel->listfield($fielddb);
            $arr['id']= $edit['id'];
            $arr['config']=serialize($config);
            $cxmodel->save($arr,$arr['id']);
            $this->addlog('更改模型字段排序成功！');
            $this->success('更改排序成功！');
        }
        $this->assign('mid',$edit['id']);
        $this->assign('list',$fielddb);
        return view();
    }
    //  添加模型字段
    public function add_field(){
        if(!request()->param('id') || !cxModel::get(input('id'))){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('ArtModel');
            if(!$validate->scene('field')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            $data = datatrim($data);
            if(!table_fields('article_content_'.$data['id'],$data['sqlname'])){
                $this->error('字段已存在或受保护，请重新填写！');
            }
            if($cxmodel->zipfield($data,$data['sqlname'])){
                $cxmodel->addfield($data);
                webCacheRm('artmodel');
                $this->addlog('添加模型字段【'.$data['title'].'】！');
                $this->success('添加模型字段【'.$data['title'].'】成功！',url('fieldlist',array('id' => $data['id'])));
            }else{
                $this->error('添加模型字段【'.$data['title'].'】失败！');
            }
        }
        $postdb['sqltype'] = 'mediumtext';
        $postdb['sqlname']="my".rand(1,999);
        $postdb['title']= "我的字段".$postdb['sqlname'];
        $group = model('AuthGroup')->grouplst();
        $this->assign([
            'mid'  => input('id'),
            'postdb'  => $postdb,
            'group'  => $group,
        ]);
        return view();
    }
    //  编辑模型字段
    public function edit_field(){
        if(!request()->param('id') || !request()->param('sname') || !cxModel::get(input('id'))){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $edit = cxModel::get(input('id'));
        $config = unserialize($edit['config']);
        $postdb = $config['field_db'][input('sname')];
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('ArtModel');
            if(!$validate->scene('field')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            $data = datatrim($data);
            if($data['sqlname'] != $postdb['sqlname']){
                if(!table_fields('article_content_'.$data['id'],$data['sqlname'])){
                    $this->error('字段已存在或受保护，请重新填写！');
                }
            }
            if($data['sqlname'] != $postdb['sqlname'] || $postdb['sqltype'] != $postdb['sqltype']){
                $cxmodel->addfield($data,"change `{$postdb['sqlname']}`");
            }
            if($cxmodel->zipfield($data,$postdb['sqlname'])){
                webCacheRm('artmodel');
                $this->addlog('修改模型字段【'.$data['title'].'】！');
                $this->success('修改模型字段【'.$data['title'].'】成功！',url('fieldlist',array('id' => $data['id'])));
            }else{
                $this->error('修改模型字段【'.$data['title'].'】失败！');
            }
        }
        $group = model('AuthGroup')->grouplst();
        $this->assign([
            'mid'  => $edit['id'],
            'postdb'  => $postdb,
            'group'  => $group,
        ]);
        return view('add_field');
    }
    //  删除模型字段
    public function del_field(){
        if(request()->isPost()){
            $data = input('post.');
            $deltab = cxModel::get($data['id']);
            $deltab = unserialize($deltab['config']);
            $title = $deltab['field_db'][$data['name']]['title'];
            $sqlname = $deltab['field_db'][$data['name']]['sqlname'];
            $tabelname = config('database.prefix').'article_content_'.$data['id'];
            $files = \db('article_content_'.$data['id'])->where($sqlname,'like','%cxbs_net%')->count();
            if($files != '0'){
                $this->error("本字段含有文件信息，请先处理！");
            }
            Db::execute("alter table `{$tabelname}` drop `{$data['name']}`");
            unset($deltab['field_db'][$data['name']]);
            $config['id']= $data['id'];
            $config['config']=serialize($deltab);
            model('ArtModel')->save($config,$config['id']);
            webCacheRm('artmodel');
            $this->addlog('删除模型字段【'.$title.'】成功！');
            $this->success('删除模型字段【'.$title.'】成功,请重新生成模板！');
        }
    }
    //  生成模板
    public function artmobe($mid){
        if(!request()->param()){
            $this->error("请选择要生成模板的模型！");
        }
        $cxmodel = new cxModel();
        if(request()->isGet()){
            $id = request()->param('mid');
            $moddata = $cxmodel->where('id',$id)->value('config');
            $moddata = unserialize($moddata);
            $cxforms = new Cxforms();
            $fields = '';
            if(empty($moddata['field_db'])){
                $this->error("没有模型字段，请先添加模型字段!");
            }
            foreach ($moddata['field_db'] as $k => $v){
                $fields .= $cxforms->modelfield($v);
            }
            $post_file = $cxforms->file_read(config('template.view_path').'article/add.htm');
            $post_tpl = str_replace("{\$Artmodel|default=''}",$fields,$post_file);
            $cxforms->file_write(ROOT_PATH."data/post/post_{$id}.htm",$post_tpl);
            $this->success('生成模板成功！');
        }
    }

    //  标准化自订义字段数组
    protected function formatdata($data){
        $data['formset'] = str_replace('，',',',$data['formset']);
        if(isset($data['seeauth'])){
            $data['seeauth'] = implode(',',$data['seeauth']);
        }
        $data['sqlname'] = strtolower($data['sqlname']);
        return $data;
    }

}
