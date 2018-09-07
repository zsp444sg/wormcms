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
use app\admin\model\User as cxModel;
use app\admin\model\UserData as usModel;
use app\admin\model\AuthGroup as groupModel;
use app\common\controller\Upload;
use app\common\extend\Cxforms;
use think\Loader;

class User extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,huishou,edit,so'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }

    public function index(){
        $usmodel = new usModel();
        $list =  $usmodel->where('status','<>','2')->order('uregtime desc,uid desc')->paginate(20)->each(function($item, $key){
            $group = model('AuthGroup')->allgroup();
            foreach ($group as $val){
                if($item['group_id'] == $val['id']){
                    $item->group_name = $val['title'];
                    break;
                }
            }
        });
        $this->assign('list',$list);
        return view();
    }
    public function huishou(){
        $usmodel = new usModel();
        $list =  $usmodel->where('status','2')->order('uregtime desc,uid desc')->paginate(20)->each(function($item, $key){
            $group = model('AuthGroup')->allgroup();
            foreach ($group as $val){
                if($item['group_id'] == $val['id']){
                    $item->group_name = $val['title'];
                }
            }
        });
        $this->assign('list',$list);
        return view();
    }
    //  添加新用户
    public function add(){
        $cxmodel = new cxModel();
        $groupmodel = new groupModel();
        $upmodel = new Upload();
        $usmodel = new usModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data['upassword'] = $data['newpassword'];
            $validate = Loader::validate('User');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['upassword'] = pwd($data['newpassword']);
            //  处理头像
            if(!empty($data['uicon']) && isset($data['uicon'])){
                $uicon = 'uicon';
                $data['uicon'] = $upmodel->fileMove($data['uicon'],$uicon,'copy',false);
            }
            //  处理自订义字段
            $data = $this->addeditbase($data);
            if($cxmodel->allowField(true)->isUpdate(false)->save($data)){
                $data['uid'] = $cxmodel->uid;
                if($usmodel->allowField(true)->isUpdate(false)->save($data)){
                    $arr = array(
                        'uid' => $data['uid'],
                        'group_id' => $data['group_id'],
                    );
                    if($cxmodel->usergroup()->save($arr)){
                        $this->addlog('添加【'.$data['username'].'】用户成功！');
                        $this->success('添加【'.$data['username'].'】用户成功！','index');
                    }else{
                        $cxmodel->delete($data['uid']);
                        usModel::destroy(['uid' => $data['uid']]);
                    }
                }else{
                    $cxmodel->delete($data['uid']);
                }
            }
            $this->addlog('添加用户失败！');
            $this->error('添加用户失败！');
        }
        $this->usertemp();
        $groupid = $groupmodel->grouplst();
        $this->assign([
            'groupid' => $groupid
        ]);
        return view();
    }
    //  编辑用户
    public function edit(){
        $cxmodel = new cxModel();
        $groupmodel = new groupModel();
        $upmodel = new Upload();
        $usmodel = new usModel();
        $getdata = request()->param();
        if(empty($getdata['uid']) || !isset($getdata['uid'])){
            $this->error("访问错误！");
        }
        $edit = $usmodel->where('uid',$getdata['uid'])->find();
        $edit = $this->usmodelfields($edit);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $data['upassword'] = $data['newpassword'];
            $validate = Loader::validate('User');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            //  处理密码
            if(!empty($data['upassword']) && isset($data['upassword'])){
                $data['upassword'] = pwd($data['newpassword']);
            }
            //  处理头像
            if(!empty($data['uicon']) && isset($data['uicon'])){
                if($data['uicon'] != $edit['uicon']){
                    $uicon = 'uicon';
                    $data['uicon'] = $upmodel->fileMove($data['uicon'],$uicon,'copy',false);
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$edit['uicon']);
                }
            }else{
                if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$edit['uicon'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$edit['uicon']);
                }
            }
            //  处理自订义字段
            $data = $this->addeditbase($data,$edit);
            if(!empty($data['upassword']) && isset($data['upassword'])){
                $password = $data['upassword'];
            }
            if($data['uid'] == '1' && $this->cxbsuser['open'] != '1'){
                unset($data['upassword']);
            }
            if($usmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                if($data['uid'] == '1' && $this->cxbsuser['open'] == '1'){

                }else{
                    if(!empty($password) && isset($password)){
                        $data['upassword'] = $password;
                        $cxmodel->allowField(true)->isUpdate(true)->save($data,$data['uid']);
                    }
                }
                db('auth_group_access')->where('uid',$data['uid'])->setField('group_id',$data['group_id']);
                $this->addlog('修改【'.$edit['username'].'】用户成功！');
                $this->success('修改【'.$edit['username'].'】用户成功！','index');
            }
            $this->addlog('修改用户失败！');
            $this->error('修改用户失败！');
        }
        $this->usertemp();
        $groupid = $groupmodel->grouplst();
        $this->assign([
            'groupid' => $groupid,
            'postdb' => $edit
        ]);
        return view('add');
    }
    //  搜索用户
    public function so(){
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        $so['username|uniname|uemail|uphone|uicq|uaddress'] = array('like',"%".$getdata['keyword']."%");
        $usmodel = new usModel();
        $list = $usmodel->where($so)->where('status','<>','2')->order('uid desc')->paginate('20',false,['query' => request()->param()])->each(function($item, $key){
            $group = model('AuthGroup')->allgroup();
            foreach ($group as $val){
                if($item['group_id'] == $val['id']){
                    $item->group_name = $val['title'];
                }
            }
        });
        $this->assign('list',$list);
        return view('index');
    }
    //  更改状态
    public function see(){
        if(request()->isPost()){
            $uid = input('cxbsid');
            if($uid == 1){
                $this->error('此用户不得禁用！');
            }
            $usmodel = new usModel();
            $status = $usmodel->where('uid',$uid)->find();
            if($status['status'] == 1){
                usModel::update(['id' => $status['id'],'status' => '0']);
                $this->success("禁用用户【{$status['username']}】成功");
            }else{
                usModel::update(['id' => $status['id'],'status' => '1']);
                $this->success("启用用户【{$status['username']}】成功");
            }
        }
        $this->error('访问错误!');
    }
    //  删除用户
    public function del(){
        if(request()->isPost()){
            if(input('id') == 1){
                $this->error('此用户禁止删除！');
            }
            if(input('id') == session('_admin_.uid')){
                $this->error('你不能删除自己，请联系管理员！');
            }
            $cxmodel = new cxModel();
            $name = $cxmodel->userdb()->where('uid',input('id'))->find();
            if($name['status'] == '0' || $name['status'] == '1'){
                $cxmodel->userdb()->where('uid',$name['uid'])->update(['status'=>'2']);
                $this->success('删除【'.$name['username'].'】用户至回收站！');
            }
            if($name['uicon']){
                if(is_file(ROOT_PATH.$this->webdb['updir'].$name['uicon'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].$name['uicon']);
                }
            }
            if($this->deluser(input('id'))){
                $this->addlog('删除【'.$name['username'].'】用户成功！');
                $this->success('删除【'.$name['username'].'】用户成功！');
            }else{
                $this->addlog('删除【'.$name['username'].'】用户失败！');
                $this->error('删除【'.$name['username'].'】用户失败！');
            }
        }
        $this->error('访问错误！');
    }
    public function dsee(){
        if(request()->isPost()){
            $data = input('post.');
            $cxmodel = new cxModel();
            $name = $cxmodel->userdb()->where('uid',(int)$data['cxbsid'])->find();
            $cxmodel->userdb()->where('uid',$name['uid'])->update(['status'=>'1']);
            $this->success("还原用户【{$name['username']}】成功！");
        }
        $this->error('访问错误！');
    }
    //  批量删除至回收站
    public function edel(){
        if(request()->isPost()) {
            $data = input('post.');
            $cxmodel = new cxModel();
            foreach ($data['delid'] as $v){
                $cxmodel->userdb()->where('uid',$v)->update(['status'=>'2']);
            }
            $this->addlog('删除至回收站成功！');
            $this->success('删除至回收站成功!');
        }
        $this->error('访问错误！');
    }
    //  批量删除
    public function pdel(){
        if(request()->isPost()) {
            $data = input('post.');
            $cxmodel = new cxModel();
            foreach ($data['delid'] as $v){
                $name = $cxmodel->userdb()->where('uid',$v)->find();
                if($name['uicon']){
                    if(is_file(ROOT_PATH.$this->webdb['updir'].$name['uicon'])){
                        @unlink(ROOT_PATH.$this->webdb['updir'].$name['uicon']);
                    }
                }
                $deldata[] = $name['username'];
                $this->deluser($name['uid']);
            }
            $deldata = implode(',',$deldata);
            $this->addlog("删除用户【{$deldata}】成功！");
            $this->success('删除用户成功!');
        }
        $this->error('访问错误！');
    }
    //  删除关联数据
    protected function deluser($uid){
        $user = cxModel::get($uid);
        if ($user->delete()){
            $user->userdb->delete();
            $user->usergroup->delete();
            return true;
        } else {
            return false;
        }
    }
    //  获取自订义字段
    protected function userbase(){
        $userdata = unserialize($this->webdb['userdata']);
        if(!$userdata){
            return null;
        }
        $cxforms = new Cxforms();
        $userdata = $cxforms->rarfield($userdata);
        return $userdata;
    }
    //  生成自订义模板
    protected function usertemp(){
        $cxforms = new Cxforms();
        if(!is_file(ROOT_PATH."cxadmin/template/user/userdata.htm")){
            $userbase = $this->userbase();
            if($userbase){
                $userbase = implode('',$userbase);
            }else{
                $userbase = '';
            }
            $cxforms->file_write(ROOT_PATH."cxadmin/template/user/userdata.htm",$userbase);
        }
        return true;
    }
    //  处理自订义字段
    protected function addeditbase($data,$olddata=''){
        $userdata = unserialize($this->webdb['userdata']);
        if(!$userdata){
            return $data;
        }
        $fielddb = $userdata['field_db'];
        $upmodel = new Upload();
        $pirdir = date("Ym");
        foreach ($fielddb as $k => $v){
            if(!isset($data[$k]) || empty($data[$k]) || $data[$k] == null){
                continue;
            }
            if($v['formrequired'] == '1'){
                if(empty($data[$k]) || !isset($data[$k])){
                    $this->error("{$v['title']}不得为空");
                }
            }
            if(is_array($data[$k]) || isset($data[$k])){
                switch ($v['formtype']){
                    case 'textarea':
                        $data[$k] = str_replace('，',',',$data[$k]);
                        $data[$k] = preg_replace("/\r\n/","<br>",$data[$k]);
                        break;
                    case 'upfile':
                        if(!empty($olddata[$k])){
                            if($data[$k] == $olddata[$k]){
                                $data[$k] = $upmodel->editadd($data[$k]);
                                continue;
                            }else{
                                @unlink(ROOT_PATH.$this->webdb['updir'].$olddata[$k]);
                            }
                        }
                        $arr = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data[$k],$pirdir);
                        $data[$k] = $upmodel->editadd($arr);
                        unset($arr);
                        break;
                    case 'upmv':
                        if(!empty($olddata[$k])){
                            if($data[$k] == $olddata[$k]){
                                $data[$k] = $upmodel->editadd($data[$k]);
                                continue;
                            }else{
                                @unlink(ROOT_PATH.$this->webdb['updir'].$olddata[$k]);
                            }
                        }
                        $arr = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($data[$k],$pirdir);
                        $data[$k] = $upmodel->editadd($arr);
                        unset($arr);
                        break;
                    case 'uptxt':
                        $oldfiles = $newfiles = '';
                        $arr = '';
                        if(!empty($olddata[$k])){
                            foreach ($olddata[$k] as $val){
                                $oldfiles[] = $val['value'];
                            }
                        }
                        foreach ($data[$k] as $key => $val){
                            if(!empty($oldfiles)){
                                if(in_array($val,$oldfiles)){
                                    $newfiles[] = $val;
                                    $val = $upmodel->editadd($val);
                                    $arr[] = $key.'@@@'.$val;
                                    continue;
                                }
                            }
                            $newfiles[] = $val;
                            $val = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($val,$pirdir);
                            $val = $upmodel->editadd($val);
                            $arr[] = $key.'@@@'.$val;
                        }
                        if(!empty($oldfiles)){
                            foreach ($oldfiles as $val){
                                if(!in_array($val,$newfiles)){
                                    @unlink(ROOT_PATH.$this->webdb['updir'].$val);
                                }
                            }
                        }
                        $oldfiles = $newfiles = null;
                        $data[$k] = implode('&@&@&',$arr);
                        unset($arr);
                        break;
                    case 'upimg':
                        $oldfiles = $newfiles = '';
                        $arr = '';
                        if(!empty($olddata[$k])){
                            foreach ($olddata[$k] as $val){
                                $oldfiles[] = $val['value'];
                            }
                        }
                        foreach ($data[$k] as $key => $val){
                            if(!empty($oldfiles)){
                                if(in_array($val,$oldfiles)){
                                    $newfiles[] = $val;
                                    $val = $upmodel->editadd($val);
                                    $arr[] = $key.'@@@'.$val;
                                    continue;
                                }
                            }
                            $newfiles[] = $val;
                            $val = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($val,$pirdir);
                            $val = $upmodel->editadd($val);
                            $arr[] = $key.'@@@'.$val;
                        }
                        if(!empty($oldfiles)){
                            foreach ($oldfiles as $val){
                                if(!in_array($val,$newfiles)){
                                    @unlink(ROOT_PATH.$this->webdb['updir'].$val);
                                }
                            }
                        }
                        $oldfiles = $newfiles = null;
                        $data[$k] = implode('&@&@&',$arr);
                        unset($arr);
                        break;
                    case 'uppaly':
                        $oldfiles = $newfiles = '';
                        $arr = '';
                        if(!empty($olddata[$k])){
                            foreach ($olddata[$k] as $val){
                                $oldfiles[] = $val['value'];
                            }
                        }
                        foreach ($data[$k] as $key => $val){
                            if(!empty($oldfiles)){
                                if(in_array($val,$oldfiles)){
                                    $newfiles[] = $val;
                                    $val = $upmodel->editadd($val);
                                    $arr[] = $key.'@@@'.$val;
                                    continue;
                                }
                            }
                            $newfiles[] = $val;
                            $val = '/'.$this->webdb['updir'].'/'.$upmodel->fileMove($val,$pirdir);
                            $val = $upmodel->editadd($val);
                            $arr[] = $key.'@@@'.$val;
                        }
                        if(!empty($oldfiles)){
                            foreach ($oldfiles as $val){
                                if(!in_array($val,$newfiles)){
                                    @unlink(ROOT_PATH.$this->webdb['updir'].$val);
                                }
                            }
                        }
                        $oldfiles = $newfiles = null;
                        $data[$k] = implode('&@&@&',$arr);
                        unset($arr);
                        break;
                    case 'checkbox':
                        $data[$k] = implode(',',$data[$k]);
                        break;
                    case 'chinacode':
                        $data[$k] = implode(',',$data[$k]);
                        break;
                }
            }
        }
        $content = null;
        foreach ($data as $k => $v){
            if($v == '0'){
                $content[$k] = $v;
                continue;
            }
            if(!isset($v) || empty($v)){
                continue;
            }
            $content[$k] = $v;
        }
        return $content;
    }
    //  对附加表进行读取解释
    protected function usmodelfields($data){
        $userdata = unserialize($this->webdb['userdata']);
        if(!$userdata){
            return $data;
        }
        $fielddb = $userdata['field_db'];
        $upmodel = new Upload();
        foreach ($fielddb as $k => $v){
            if(!isset($data[$k]) || empty($data[$k]) || $data[$k] == null){
                continue;
            }
            if($v['formtype'] == 'textarea'){
                $data[$k] = preg_replace('/<br>/',"\r",$data[$k]);
            }elseif($v['formtype'] == 'uptxt' || $v['formtype'] == 'upimg' || $v['formtype'] == 'upmv'){
                $data[$k] = explode('&@&@&',$data[$k]);
                $arr = array();
                foreach ($data[$k] as $key => $val){
                    $arr[] = explode('@@@',$val);
                }
                foreach ($arr as $value){
                    $arrs['title'] = $value[0];
                    $arrs['value'] = $upmodel->editadd($value[1],false);
                    $editdata[] = $arrs;
                }
                $data[$k] = $editdata;
                unset($arr);
                unset($arrs);
            }elseif($v['formtype'] == 'upfile'){
                $data[$k] = $upmodel->editadd($data[$k],false);
            }elseif($v['formtype'] == 'chinacode'){
                $data[$k] = explode(',',$data[$k]);
            }
        }
        return $data->toArray();
    }
}
