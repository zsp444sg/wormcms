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
use app\common\controller\Upload;
use app\common\model\Forms as cxModel;
use think\Loader;

class Forms extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,add,edit'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }
    public function index(){
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        $list = db('forms_cont_'.$getdata['fid'])->order('addtime desc,id desc')->paginate(20);
        $this->assign([
           'list' => $list,
        ]);
        return view(ROOT_PATH.'cxadmin/template/forms/list_'.$getdata['fid'].'.htm');
    }
    public function add(){
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Forms');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $fid =  $data['fid'];
            $data = $this->modelfields($data);
            $data['addtime'] = time();
            $data['addip'] = request()->ip();
            $data['uid'] = $this->cxbsuser['uid'];
            $data['username'] = $this->cxbsuser['username'];
            if(db('forms_cont_'.$fid)->insert($data)){
                $this->addlog('添加【'.$fid.'】表单成功');
                $this->success("添加成功！",url('index',array('fid'=>$fid)));
            }else{
                $this->error("添加数据失败！");
            }
        }
        return view(ROOT_PATH.'cxadmin/template/forms/post_'.$getdata['fid'].'.htm');
    }

    // 修改导航
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        $postdb = db('forms_cont_'.$getdata['fid'])->where('id',$getdata['id'])->find();
        $postdb['fid'] = $getdata['fid'];
        $postdb = $this->usmodelfields($postdb);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Forms');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $fid =  $data['fid'];
            $data = $this->modelfields($data,$postdb);
            $data['edittime'] = time();
            $data['editip'] = request()->ip();
            if(db('forms_cont_'.$fid)->where('id',$data['id'])->update($data)){
                $this->success('修改表单成功',url('index',array('fid'=>$fid)));
            }else{
                $this->error('修改表单【'.$data['title'].'】失败');
            }
        }
        $this->assign('postdb',$postdb);
        return view(ROOT_PATH.'cxadmin/template/forms/art_'.$getdata['fid'].'.htm');
    }
    //  删除
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $getdata = request()->param();
            $deldata = db('forms_cont_'.$getdata['fid'])->where('id',$getdata['id'])->find();
            $deldata['fid'] = $getdata['fid'];
            $deldata = $this->usmodelfields($deldata);
            $deldata = $this->delmodelfields($deldata);
            if(db('forms_cont_'.$getdata['fid'])->delete($getdata['id'])){
                $this->success("删除表单成功！");
            }else{
                $this->error("删除表单失败！");
            }
        }
        return;
    }
    //  处理模型字段
    protected function modelfields($data,$olddata=''){
        $model = model('ArtForms')->artmodel();
        foreach ($model as $val){
            if($val['id'] == $data['fid']){
                $model = $val;
                break;
            }
        }
        $cxmodel = new cxModel();
        //  定义基本属性
        $model = unserialize($model['conf']);
        $fielddb = $model['field_db'];
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
        $cont = $cxmodel->futabel($data['fid']);
        $content = null;
        foreach ($cont as $k => $v){
            if(!isset($data[$v])){
                continue;
            }
            $content[$v] = $data[$v];
        }
        return $content;
    }
    //  对附加表进行读取解释
    public function usmodelfields($data){
        $model = model('ArtForms')->artmodel();
        foreach ($model as $val){
            if($val['id'] == $data['fid']){
                $model = $val;
                break;
            }
        }
        $upmodel = new Upload();
        $model = unserialize($model['conf']);
        $fielddb = $model['field_db'];

        foreach ($fielddb as $k => $v){
            if(!isset($data[$k]) || empty($data[$k]) || $data[$k] == null){
                continue;
            }
            if($v['formtype'] == 'textarea'){
                $data[$k] = preg_replace('/<br>/',"\r",$data[$k]);
            }elseif($v['formtype'] == 'uptxt' || $v['formtype'] == 'upimg' || $v['formtype'] == 'upmv'){
                $data[$k] = explode('&@&@&',$data[$k]);
                $arr = '';
                foreach ($data[$k] as $key => $val){
                    $arr[] = explode('@@@',$val);
                }
                $data[$k] = null;
                foreach ($arr as $value){
                    $arrs['title'] = $value[0];
                    $arrs['value'] = $upmodel->editadd($value[1],false);
                    $data[$k][] = $arrs;
                }
                unset($arr);
                unset($arrs);
            }elseif($v['formtype'] == 'upfile'){
                $data[$k] = $upmodel->editadd($data[$k],false);
            }elseif($v['formtype'] == 'chinacode'){
                $data[$k] = explode(',',$data[$k]);
            }
        }
        return $data;
    }
    //  删除附加表中的内容
    public function delmodelfields($data){
        $model = model('ArtForms')->artmodel();
        foreach ($model as $val){
            if($val['id'] == $data['fid']){
                $model = $val;
                break;
            }
        }
        $model = unserialize($model['conf']);
        $fielddb = $model['field_db'];
        foreach ($fielddb as $k => $v){
            if(!isset($data[$k]) || empty($data[$k]) || $data[$k] == null){
                continue;
            }
            if($v['formtype'] == 'uptxt' || $v['formtype'] == 'upimg' || $v['formtype'] == 'upmv'){
                foreach ($data[$k] as $val){
                    @unlink(ROOT_PATH.$val['value']);
                }
            }elseif($v['formtype'] == 'upfile'){
                @unlink(ROOT_PATH.$data[$k]);
            }elseif($v['formtype'] == 'ieedit'){
                $imgps = "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
                preg_match_all($pattern = $imgps, $data[$k], $donimg);//获得老的图片
                if(!empty($donimg)){
                    $delimg = array_flip($donimg[1]);
                    $delimg = array_flip($delimg);
                    foreach ($delimg as $val){
                        @unlink(ROOT_PATH.$val);
                    }
                }
            }
        }
        return true;
    }
}
