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
use app\admin\model\AuthRule as cxModel;
use think\Loader;

class AuthRule extends Adminbase {
    /*
     * 验证按钮权限
     */
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so,add,edit'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }
    public function index(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel->sorts($data);
            $this->addlog('更改排序成功！');
            webCacheRm('auth');
            $this->success('更改排序成功！','index');
        }
        $sort = $cxmodel->sort('list');
        $this->assign('list',$sort);
        return view();
    }
    // 添加权限
    public function add(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('AuthRule');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            //  查询是否启用和是否为开发者模式
            if($data['pid'] != 0){
                $updata =$cxmodel->where('id',$data['pid'])->find();
                switch ($updata['status']){
                    case 0:
                        $data['status'] = '0';
                        break;
                }
                switch ($updata['open']){
                    case 0:
                        $data['open'] = '0';
                        break;
                }
            }
            if($data['pid'] != 0){
                $data['level'] = $cxmodel->where('id',$data['pid'])->value('level')+1;
            }else{
                $data['level'] = '1';
            }
            if($cxmodel->allowField(true)->isUpdate(false)->save($data)){
                $this->addlog("添加[{$data['title']}]权限成功！");
                webCacheRm('auth');
                $this->success("添加[{$data['title']}]权限成功！",'index');
            }else{
                $this->error('添加权限失败！');
            }
        }
        $sort = $cxmodel->sort('drop');
        $this->assign('pidauthrule',$sort);
        return view();
    }
    // 修改权限
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $id = trim(request()->param('id'));
        $postdb = $cxmodel->where('id',$id)->find();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('AuthRule');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            //  查询上下级权限
            if($data['pid'] != $postdb['pid']){
                $uplevel = $cxmodel->where('id',$data['pid'])->value('level');
                if($data['pid'] != 0 && !empty($uplevel) && isset($uplevel)){
                    $data['level'] = $uplevel+1;
                }else{
                    $data['level'] = '1';
                }
                $dlist = $cxmodel->sort('down',$data['id']);
                if(isset($dlist) && !empty($dlist)){
                    $level = $this->dolevel($dlist,$data);
                    if($level == false){
                        $this->error('系统仅支持3级权限管理,请重新选择上级权限!');
                    }
                    $cxmodel->saveAll($level);
                }
            }
            if($data['status'] != $postdb['status'] || $data['open'] != $postdb['open']){
                if($data['pid'] != 0){
                    $data = $this->donauth($data,'status,open',true);
                }else{
                    $data = $this->donauth($data,'status,open');
                }
            }
            if($cxmodel->allowField(true)->isUpdate(true)->save($data)){
                $this->addlog('修改['.$data['title'].']权限成功！');
                webCacheRm('auth');
                $this->success('修改['.$data['title'].']权限成功！');
            }else{
                $this->error('修改['.$data['title'].']权限失败！');
            }
        }
        $pidauth = $cxmodel->sort('drop');
        if($postdb['pid'] !== 0){
            $upstatus = $cxmodel->where('id',$postdb['pid'])->value('status');
            switch ($upstatus){
                case 0:
                    $postdb['upstatus'] = 'disabled="disabled"';
                    break;
            }
        }
        $this->assign('pidauthrule',$cxmodel->editpid($pidauth,$id));
        $this->assign('postdb',$postdb);
        return view('add');
    }
    //  更改状态
    public function see(){
        if(request()->isPost()){
            $id = input('cxbsid');
            $cxmodel = new cxModel();
            $lst = $cxmodel->sort('down',$id);
            $status = cxModel::get($id);
            if($status['status'] == 1){
                if($lst){
                    foreach ($lst as $k => $v){
                        cxModel::update(['id' => $v['id'],'status' => '0']);
                    }
                }
                cxModel::update(['id' => $id,'status' => '0']);
                webCacheRm('auth');    // 清除缓存
                return $this->success("禁用权限成功！");
            }else{
                $upsatus = $cxmodel->where('id',$status['pid'])->value('status');
                if($upsatus === 0){
                    return $this->error("请先启用上级权限！");
                }
                if($lst){
                    foreach ($lst as $k => $v){
                        $coed[] = $v['id'];
                        cxModel::update(['id' => $v['id'],'status' => '1']);
                    }
                }
                cxModel::update(['id' => $id,'status' => '1']);
                webCacheRm('auth');    // 清除缓存
                return $this->success("启用权限成功！");
            }
        }
        $this->error('非法操作！');
    }
    // 删除权限
    public function del(){
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $dellist =$cxmodel->sort('down',input('id'));
            $title = $cxmodel->where('id',input('id'))->value('title');
            if($dellist){
                foreach ($dellist as $k => $v){
                    cxModel::destroy($v['id']);
                }
            }
            if($del = cxModel::destroy(input('id'))){
                $this->addlog('删除【'.$title.'】权限成功！');
                webCacheRm('auth');    // 清除缓存
                $this->success('删除【'.$title.'】权限成功！');
            }else{
                $this->error('删除权限失败！');
            }
        }
        return redirect('index');
    }
    //  搜索
    public function so(){
        if(request()->isPost()){
            $data = input('post.');
            $cxmodel = new cxModel();
            $so['title|name']=array('like',"%".$data['keyword']."%");
            $list = $cxmodel->where($so)->order('sort desc,id asc')->select();
            if(!$list){
                $this->error('没有查询到你要的结果！');
            }
            $this->assign('list',$list);
            return view();
        }
        return redirect('index');
    }
//  修改下级权限级别
    protected function dolevel($dlist,$data){
        static $zlist = array();
        static $ts = array();
        foreach ($dlist as $k => $v){
            if($v['pid'] == $data['id']){
                $v['level'] = $data['level']+1;
                if($v['level'] > 3){
                    $ts[] = $v;
                }
                $zlist[] = ([
                    'id' => $v['id'],
                    'level' => $v['level'],
                ]);
                $this->dolevel($dlist,$v);
            }
        }
        if($ts){
            return false;
        }
        return $zlist;
    }
    //  更改状态
    /*
     * @param $data  权限信息
     * @param $type 查询字段
     * @param bool $up  是否查询上级栏目状态
     * @return mixed    返回权限信息
     */
    protected function donauth($data,$type,$up = false){
        $cxmodel = new cxModel();
        $type = explode(',',$type);
        //  查询上级状态，并更改当前状态
        foreach ($type as $k => $v){
            if($up == true){
                $list = $cxmodel->where('id',$data['pid'])->value($v);
                switch ($list){
                    case 0:
                        $data[$v] = $list;
                        break;
                }
            }
            //  查询下级状态，并进行更改
            switch ($data[$v]){
                case 0:
                    $dow = $cxmodel->sort('down',$data['id']);
                    if($dow){
                        foreach ($dow as $k1 => $v1){
                            cxModel::update(['id' => $v1['id'],$v => '0']);
                        }
                    }
                    break;
            }
        }
        return $data;
    }
}
