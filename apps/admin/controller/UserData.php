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
use app\admin\model\UserData as cxModel;
use app\admin\model\AuthGroup as groupModel;
use app\common\extend\Cxforms;
use think\Db;
use think\Loader;

class UserData extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,edit,so'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }

    public function index(){
        $cxmodel = new cxModel();
        $list = unserialize($this->webdb['userdata']);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data as $k => $v){
                $list['field_db'][$k]['sort'] = $v;
            }
            $list['field_db'] = $cxmodel->listfield($list['field_db']);
            $config['conf']= 'userdata';
            $config['conf_value']=serialize($list);
            model('Config')->save($config,$config['conf']);
            webCacheRm('webdb');
            if(is_file(ROOT_PATH."cxadmin/template/user/userdata.htm")){
                @unlink(ROOT_PATH."cxadmin/template/user/userdata.htm");
            }
            $this->addlog('更改用户自订义字段排序成功！');
            $this->success('更改排序成功！');
        }
        $cxforms = new Cxforms();
        if(!empty($list) && isset($list)){
            $list = $cxforms->formtext($list['field_db']);
        }
        $this->assign('list',$list);
        return view();
    }
    //  添加字段
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('UserData');
            if(!$validate->scene('adduserdata')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            $data = datatrim($data);//处理数据
            if(!table_fields('user_data',$data['sqlname'])){
                $this->error('字段已存在或受保护，请重新填写！');
            }
            if($addfield = $cxmodel->addfield($data)){
                $array = $cxmodel->zipdata($data);
                $config['conf']= 'userdata';
                $config['conf_value']=serialize($array);
                model('Config')->save($config,$config['conf']);
                webCacheRm('webdb');
                if(is_file(ROOT_PATH."cxadmin/template/user/userdata.htm")){
                    @unlink(ROOT_PATH."cxadmin/template/user/userdata.htm");
                }
                $this->addlog('添加自订义用户字段【'.$data['title'].'】！');
                $this->success('添加自订义用户字段【'.$data['title'].'】成功！','index');
            }else{
                $this->error('添加自订义用户字段【'.$data['title'].'】失败！');
            }
        }
        $postdb['sqltype'] = 'mediumtext';
        $postdb['sqlname']="my".rand(1,999);
        $postdb['title']= "我的字段".$postdb['sqlname'];
        $groupmodel = new groupModel();
        $group = $groupmodel->grouplst();
        $this->assign([
            'postdb' => $postdb,
            'group' => $group
        ]);
        return view();
    }
    //  编辑字段
    public function edit(){
        $cxmodel = new cxModel();
        $list = unserialize($this->webdb['userdata']);
        $edit= $list['field_db'][input('id')];
        if(request()->isPost()){
            $data = input('post.');
            $validate = Loader::validate('UserData');
            if(!$validate->scene('adduserdata')->check($data)){
                $this->error($validate->getError());
            }
            $data = $this->formatdata($data);
            $data = datatrim($data);//处理数据
            if($data['sqlname'] != $edit['sqlname']){
                if(!table_fields('user_data',$data['sqlname'])){
                    $this->error('字段已存在或受保护，请重新填写！');
                }
            }
            if($data['sqlname'] != $edit['sqlname'] || $data['sqltype'] != $edit['sqltype']){
                $cxmodel->addfield($data,"change `{$edit['sqlname']}`");
            }
            $array = $cxmodel->zipdata($data,$edit['sqlname']);
            $config['conf']= 'userdata';
            $config['conf_value']=serialize($array);
            if(model('Config')->save($config,$config['conf'])){
                webCacheRm('webdb');
                if(is_file(ROOT_PATH."cxadmin/template/user/userdata.htm")){
                    @unlink(ROOT_PATH."cxadmin/template/user/userdata.htm");
                }
                $this->addlog('修改【'.$edit['title'].'】字段【'.$data['title'].'】！');
                $this->success('修改【'.$edit['title'].'】字段【'.$data['title'].'】成功！','index');
            }else{
                $this->error('修改自订义用户字段【'.$edit['title'].'】失败！');
            }
        }
        $groupmodel = new groupModel();
        $group = $groupmodel->grouplst();
        $this->assign([
            'postdb' => $edit,
            'group' => $group
        ]);
        return view('add');
    }
    //  删除
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $data = input('id');
            $deltab = unserialize($this->webdb['userdata']);
            $title = $deltab['field_db'][$data]['title'];
            $sqlname = $deltab['field_db'][$data]['sqlname'];
            $tabelname = config('database.prefix').'user_data';
            $files = \db('user_data')->where($sqlname,'like','%cxbs_net%')->count();
            if($files != '0'){
                $this->error("本字段含有文件信息，请先处理！");
            }
            Db::execute("alter table `{$tabelname}` drop `{$data}`");
            unset($deltab['field_db'][$data]);
            $config['conf']= 'userdata';
            $config['conf_value']=serialize($deltab);
            model('Config')->save($config,$config['conf']);
            if(is_file(ROOT_PATH."cxadmin/template/user/userdata.htm")){
                @unlink(ROOT_PATH."cxadmin/template/user/userdata.htm");
            }
            webCacheRm('webdb');
            $this->addlog('删除用户字段【'.$title.'】成功！');
            $this->success('删除用户字段【'.$title.'】成功！');
        }
        return redirect('index');
    }
    //  标准化数据
    protected function formatdata($data){
        $data['formset'] = str_replace('，',',',$data['formset']);
        if(isset($data['seeauth'])){
            $data['seeauth'] = implode(',',$data['seeauth']);
        }
        $data['sqlname'] = strtolower($data['sqlname']);
        return $data;
    }
}
