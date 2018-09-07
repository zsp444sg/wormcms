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
use app\common\model\ArtForms as cxModel;
use app\common\extend\Cxforms;
use think\Db;
use think\Loader;

class ArtForms extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,edit,fieldlist,add_field,edit_field,del_field'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,add_field,edit_field,del_field');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        $list = $cxmodel->artmodel();
        foreach ($list as $k => $v){
            $v['formnum'] = \db('forms_cont_'.$v['id'])->count();
        }
        $this->assign('list',$list);
        return view();
    }
    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $cxmodel = new cxModel();
            $data = datatrim($data);
            $validate = Loader::validate('ArtForms');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['postgroup']) && isset($data['postgroup'])){
                foreach ($data['postgroup'] as $v){
                    $arrs['num'] = $v;
                    if(!$validate->scene('num')->check($arrs)){
                        $this->error($validate->getError());
                    }
                }
                $data['postgroup'] = implode(',',$data['postgroup']);
            }
            if(!empty($data['seegroup']) && isset($data['seegroup'])){
                foreach ($data['seegroup'] as $v){
                    $arrs['num'] = $v;
                    if(!$validate->scene('num')->check($arrs)){
                        $this->error($validate->getError());
                    }
                }
                $data['seegroup'] = implode(',',$data['seegroup']);
            }
            $data['uid'] = $this->cxbsuser['uid'];
            $data['username'] = $this->cxbsuser['username'];
            if($cxmodel->allowField(true)->save($data)){
                $data['id'] = $cxmodel->id;
                $cxmodel->addtable($data);
                webCacheRm('artform');
                $this->addlog('添加【'.$data['title'].'】模型成功');
                $this->success('添加【'.$data['title'].'】模型成功','index');
            }else{
                $this->error('添加【'.$data['title'].'】模型失败');
            }
        }
        $grouplist = model('AuthGroup')->grouplst();
        $this->assign([
            'grouplist' => $grouplist,
        ]);
        return view();
    }
    //  修改模型基本信息
    public function edit($id){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $edit = cxModel::get($id);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data); // 处理数据
            $validate = Loader::validate('ArtForms');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['postgroup']) && isset($data['postgroup'])){
                foreach ($data['postgroup'] as $v){
                    $arrs['num'] = $v;
                    if(!$validate->scene('num')->check($arrs)){
                        $this->error($validate->getError());
                    }
                }
                $data['postgroup'] = implode(',',$data['postgroup']);
            }
            if(!empty($data['seegroup']) && isset($data['seegroup'])){
                foreach ($data['seegroup'] as $v){
                    $arrs['num'] = $v;
                    if(!$validate->scene('num')->check($arrs)){
                        $this->error($validate->getError());
                    }
                }
                $data['seegroup'] = implode(',',$data['seegroup']);
            }
            $cxmodel = new cxModel();
            if($cxmodel->allowField(true)->save($data,$data['id'])){
                webCacheRm('artform');
                $this->addlog('修改【'.$data['title'].'】模型成功');
                $this->success('修改【'.$data['title'].'】模型成功','index');
            }else{
                $this->error('修改【'.$data['title'].'】模型失败');
            }
        }
        $grouplist = model('AuthGroup')->grouplst();
        $this->assign([
            'postdb' => $edit,
            'grouplist' => $grouplist,
        ]);
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
            $cxmodel = new cxModel();
            $cxmodel->saveAll($list);
            webCacheRm('artform');
            $this->addlog('更改表单模型排序成功！');
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
                webCacheRm('artform');
                $this->addlog('禁用表单【'.$see['title'].'】模型！');
                $this->success("禁用【{$see['title']}】模型！");
            }else{
                cxModel::update(['id' => $see['id'],'status' => '1']);
                webCacheRm('artform');
                $this->addlog('启用表单【'.$see['title'].'】模型！');
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
            //  查询是否有内容存在
            $partnum = \db('forms_cont_'.$deldata['id'])->count();
            if($partnum > 0){
                $this->error("模型存在内容，请处理后再行删除！");
            }
            webCacheRm('artform');
            $cxmodel->delfield($deldata);
            $deldata->delete();
            $this->addlog("删除表单模型【{$title}】成功！");
            $this->success("删除模型【{$title}】成功！");

        }
        $this->error('访问错误！');
    }
    //  模型字段列表
    public function fieldlist(){
        if(!request()->param('id') || !cxModel::get(input('id'))){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $edit = cxModel::get(input('id'));
        $conf = $fielddb = null;
        if(!empty($edit['conf']) && isset($edit['conf'])){
            $cxforms = new Cxforms();
            $conf = unserialize($edit['conf']);
            $fielddb = $cxforms->formtext($conf['field_db']);
        }
        if(request()->isPost()){
            $data = input('post.');
            foreach ($data as $k => $v){
                $fielddb[$k]['sort'] = $v;
            }
            $conf['field_db'] = $cxmodel->listfield($fielddb);
            $arr['id']= $edit['id'];
            $arr['conf']=serialize($conf);
            $cxmodel->save($arr,$arr['id']);
            $this->addlog('更改模型字段排序成功！');
            $this->success('更改排序成功！');
        }
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
            $data = datatrim($data);
            $validate = Loader::validate('ArtForms');
            if(!$validate->scene('field')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            if(!table_fields('forms_cont_'.$data['id'],$data['sqlname'])){
                $this->error('字段已存在或受保护，请重新填写！');
            }
            if($cxmodel->zipfield($data,$data['sqlname'])){
                $cxmodel->addfield($data);
                webCacheRm('artform');
                $this->addlog('添加模型字段【'.$data['title'].'】！');
                $this->success('添加模型字段【'.$data['title'].'】成功！',url('fieldlist',array('id' => $data['id'])));
            }else{
                $this->error('添加模型字段【'.$data['title'].'】失败！');
            }
        }
        $postdb['id'] = request()->param('id');
        $postdb['sqltype'] = 'mediumtext';
        $postdb['sqlname']="my".rand(1,999);
        $postdb['title']= "我的字段".$postdb['sqlname'];
        $group = model('AuthGroup')->grouplst();
        $this->assign([
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
        $conf = unserialize($edit['conf']);
        $postdb = $conf['field_db'][input('sname')];
        $postdb['id'] = $edit['id'];
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('ArtForms');
            if(!$validate->scene('field')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            $data = datatrim($data);
            if($data['sqlname'] != $postdb['sqlname']){
                if(!table_fields('forms_cont_'.$data['id'],$data['sqlname'])){
                    $this->error('字段已存在或受保护，请重新填写！');
                }
            }
            if($data['sqlname'] != $postdb['sqlname'] || $postdb['sqltype'] != $postdb['sqltype']){
                $cxmodel->addfield($data,"change `{$postdb['sqlname']}`");
            }
            if($cxmodel->zipfield($data,$postdb['sqlname'])){
                webCacheRm('artform');
                $this->addlog('修改模型字段【'.$data['title'].'】！');
                $this->success('修改模型字段【'.$data['title'].'】成功！',url('fieldlist',array('id' => $data['id'])));
            }else{
                $this->error('修改模型字段【'.$data['title'].'】失败！');
            }
        }
        $group = model('AuthGroup')->grouplst();
        $this->assign([
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
            $deltab = unserialize($deltab['conf']);
            $title = $deltab['field_db'][$data['name']]['title'];
            $sqlname = $deltab['field_db'][$data['name']]['sqlname'];
            $tabelname = config('database.prefix').'forms_cont_'.$data['id'];
            $files = \db('forms_cont_'.$data['id'])->where($sqlname,'like','%cxbs_net%')->count();
            if($files != '0'){
                $this->error("本字段含有文件信息，请先处理！");
            }
            Db::execute("alter table `{$tabelname}` drop `{$data['name']}`");
            unset($deltab['field_db'][$data['name']]);
            $config['id']= $data['id'];
            $config['conf']=serialize($deltab);
            $cxmodel = new cxModel();
            $cxmodel->save($config,$config['id']);
            webCacheRm('artform');
            $this->addlog('删除模型字段【'.$title.'】成功！');
            $this->success('删除模型字段【'.$title.'】成功,请重新生成模板！');
        }
    }
    //  生成模板
    public function artmobe(){
        if(!request()->param()){
            $this->error("请选择要生成模板的模型！");
        }
        $cxmodel = new cxModel();
        if(request()->isGet()){
            $id = request()->param('id');
            $moddata = $cxmodel->where('id',$id)->value('conf');
            $moddata = unserialize($moddata);
            $cxforms = new Cxforms();
            $fields = $tabelth = $tabeltd = $art = '';
            if(empty($moddata['field_db'])){
                $this->error("没有模型字段，请先添加模型字段!");
            }
            foreach ($moddata['field_db'] as $k => $v){
                $fields .= $cxforms->modelfield($v);
                $tabelth .= $cxforms->modelth($v);
                $tabeltd .= $cxforms->modeltd($v);
                $art .= $cxforms->modelarticle($v);
            }
            $post_file = $cxforms->file_read(config('template.view_path').'forms/postbase.htm');
            $post_tpl = str_replace("{\$Artmodel|default=''}",$fields,$post_file);
            //  生成后台列表页
            $list_file = $cxforms->file_read(config('template.view_path').'forms/listbase.htm');
            $list_tpl = str_replace("{\$Artmodelth|default=''}",$tabelth,$list_file);
            $list_tpl = str_replace("{\$Artmodeltd|default=''}",$tabeltd,$list_tpl);
//            halt($list_tpl);
            //  生成后台内容页
            $art_file = $cxforms->file_read(config('template.view_path').'forms/artbase.htm');
            $art_tpl = str_replace("{\$Artmodel|default=''}",$art,$art_file);
            //  写入模板
            $cxforms->file_write(ROOT_PATH."cxadmin/template/forms/post_{$id}.htm",$post_tpl);
            $cxforms->file_write(ROOT_PATH."cxadmin/template/forms/list_{$id}.htm",$list_tpl);
            $cxforms->file_write(ROOT_PATH."cxadmin/template/forms/art_{$id}.htm",$art_tpl);
            $this->success('生成模板成功！','index');
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
