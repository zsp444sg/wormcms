<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-07
 * Time: 11:17
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\admin\model\FuPart as cxModel;
use app\common\controller\Upload;
use think\Loader;

class FuPart extends Adminbase {
    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so,edit,add,nav,navadd'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del');
        $this->assign('btn',$btn);
    }
//    首页
    public function index($pid='0'){
        if(request()->isGet()){
            $getdata = request()->param();
        }
        if(empty($getdata['pid']) || !isset($getdata['pid'])){
            $getdata['pid'] = $pid;
        }
        $cxmodel = new cxModel();
        $list = $cxmodel->sort('list',$getdata);
        if(!$list){
            $list = '';
        }
        $this->assign([
            'list' => $list,
        ]);
        return view();
    }
    //  搜索
    public function so(){
        $cxmodel = new cxModel();
        $data = request()->param();
        $so['title'] = array('like',"%".$data['keyword']."%");
        $list =  $cxmodel->where($so)->order('sort desc,id asc')->paginate(50)->each(function($item, $key){
            $item->articlenum = model('Article')->where('fuid',$item['id'])->count();
        });
        if(!$list){
            $this->error('没有查询到你要的结果！');
        }
        $this->assign('list',$list);
        return view('index');
    }
    //  添加栏目
    public function add($pid = 0){
        if(request()->isGet()){
            $getdata = request()->param();
        }
        if(empty($getdata['pid']) || !isset($getdata['pid'])){
            $getdata['pid'] = $pid;
        }
        $cxmodel = new cxModel();
        if (request()->isPost()){
            $data = input('post.');
            $data['title'] = datatrim(explode("\r\n",str_replace(array("\r\n", "\r", "\n"), "\r\n", $data['title'])));
            $validate = Loader::validate('FuPart');
            foreach ($data['title'] as $v){
                $arrs['title'] = $v;
                if(!$validate->scene('title')->check($arrs)){
                    $this->error($validate->getError());
                }
            }
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if($data['pid'] != '0'){
                $level = $cxmodel->where('id',$data['pid'])->find();
            }
            if(!empty($level) || isset($level)){
                $data['level'] = $level['level'] + 1;
            }else{
                $data['level'] = '1';
            }
            $title = null;
            foreach($data['title'] as $k => $v){
                if(empty($v) || !isset($v) || $v == null){
                    continue;
                }
                $title .= $v;
                $adds[] = ['pid'=>$data['pid'],'class'=>$data['class'], 'title'=>$v, 'level'=>$data['level']];
            }
            if($cxmodel->saveAll($adds)){
                $this->addlog("批量添加辅栏目【{$title}】");
                webCacheRm('fupart');
                $this->success('批量添加辅栏目成功！',url('index'));
            }else{
                $this->error('批量添加辅栏目失败！');
            }
        }
        //  获取模型及分类信息
        $postdb['pids'] = $cxmodel->sort('uplist',$getdata);
        $this->assign([
            'postdb' => $postdb,
        ]);
        return view();
    }
//    编辑栏目
    public function edit(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        $cxmodel = new cxModel();
        $edit = cxModel::get((int)request()->param('id'))->toArray();
        $edit['plates'] = $this->tempnum($edit['plate']);
        $edit['temps'] = unserialize($edit['template']);
        $listtqmps = unserialize($edit['listtqmp']);
        $edit['listtemp'] = $cxmodel->temps($listtqmps['temp'],$edit);
        $edit['pids'] = $cxmodel->sort('deldonlist',$edit);
        $edit['donnum'] = $cxmodel->where('pid',$edit['id'])->count();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('FuPart');
            if(!$validate->scene('edit')->check($data)){
                $this->error($validate->getError());
            }
            if(!$validate->scene('num')->check($data['listtqmp'])){
                $this->error($validate->getError());
            }
            if(!empty($data['keywords'])){
                $data['keywords'] = str_replace(array(' ','，'),',',$data['keywords']);
            }
            $data['listtqmp'] = serialize($data['listtqmp']);
            $data['template'] = serialize($data['template']);
            $data['pid'] = (int)$data['pid'];
            $data['id'] = (int)$data['id'];
            if($data['pid'] != '0'){
                $data['level'] = $cxmodel->where('id',$data['pid'])->value('level') + 1;
            }else{
                $data['level'] = 1;
            }
            if($data['plate'] == '0' || $data['plate'] == 'default'){
                $data['plate'] = null;
            }
            //  修改上下级栏目
            if($data['pid'] != $edit['pid'] && $data['class'] == '0'){
                $cxmodel->donedit($data);
            }

            if(!empty($data['logo']) && $data['logo'] != $edit['logo']){
                $upload = new Upload();
                $data['logo'] = $upload->fileMove($data['logo'],'fupart','copy');
            }
            if($cxmodel->isUpdate(true)->allowField(true)->save($data,$data['id'])){
                if(!empty($edit['logo'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].$edit['logo']);
                }
                $this->addlog('修改栏目【'.$data['title'].'】成功！');
                webCacheRm('fupart');
                $this->success('修改栏目【'.$data['title'].'】成功！',url('index'));
            }else{
                if($data['pid'] != $edit['pid'] && $data['pid'] != '0' && $edit['class'] == '0'){
                    $cxmodel->donedit($edit);
                }
                if(!empty($data['logo'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].$data['logo']);
                }
                $cxmodel->allowField(true)->save($edit,$edit['id']);
                webCacheRm('fupart');
                $this->error('修改栏目【'.$data['title'].'】失败！');
            }
        }
        $this->assign('postdb',$edit);
        return view();
    }
    //  排序
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
            webCacheRm('fupart');
            $this->addlog('更改栏目排序成功！');
            $this->success('更改排序成功!');
        }
        $this->error("访问错误！");
    }
    //  删除栏目
    public function del(){
        if(request()->isPost()){
            $data = input('post.');
            //  获取栏目信息
            $cxmodel = new cxModel();
            $deldata = cxModel::get($data['id']);
            $title = $deldata['title'];
            $artNum = model('Article')->where('fuid',$deldata['id'])->count();//获取栏目内容
            if($artNum != '0'){
                $this->error("栏目存在内容，禁止删除！");
            }
            $dondata = $cxmodel->sort('donLevel',$deldata);//获取下级栏目
            if(!empty($dondata)){
                foreach ($dondata as $v){
                    $artNum = model('Article')->where('fuid',$v['id'])->count();//获取栏目内容
                    if($artNum != '0'){
                        $this->error("栏目存在内容，禁止删除！");
                    }
                }
            }
            $this->delclck($deldata);
            if($deldata['class'] == '0' && $deldata['pid'] == '0' && $deldata['funav'] == '1'){
                model('FuPartnav')->where('fid',$deldata['id'])->delete();
            }
            if($deldata->delete()){
                foreach ($dondata as $v){
                    $dtitle = $v['title'];
                    $this->delclck($v);
                    $cxmodel->where('id',$v['id'])->delete();
                    $this->addlog('删除栏目【'.$dtitle.'】成功');
                }
                $this->addlog("删除栏目【{$title}】成功！");
                webCacheRm('fupart');
                $this->success("删除栏目【{$title}】成功！");
            }else{
                $this->error("删除栏目【{$title}】失败！");
            }
        }
        $this->error('访问错误！');
    }
    public function pdel(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data['pdel']);
            $cxmodel = new cxModel();
            //  查询是否存在内容
            foreach ($data as $k => $v){
                $deldata = $cxmodel->where('id',$v)->find(); // 查询下级分类
                $artNum = model('Article')->where('fuid',$deldata['id'])->count();//获取栏目内容
                if($artNum != '0'){
                    $this->error('栏目存在内容，请处理后再行删除！');
                }
                if($deldata['class'] == '0'){
                    $dondata = $cxmodel->sort('donLevel',$deldata);
                    if(!empty($dondata) || isset($dondata)){
                        foreach ($dondata as $val){
                            $artNum = model('Article')->where('fuid',$val['id'])->count();//获取栏目内容
                            if($artNum != '0'){
                                $this->error("栏目存在内容，请处理后再行删除！");
                            }
                        }
                    }
                }
            }
            //  删除选中栏目
            foreach ($data as $k => $v){
                $finds = cxModel::get($v);
                $this->delclck($finds);
                $title = $finds['title'];
                if($finds['class'] == '0' && $finds['pid'] == '0' && $finds['funav'] == '1'){
                    model('FuPartnav')->where('fid',$finds['id'])->delete();
                }
                $cxmodel->where('id',$v)->delete();
                $this->addlog("删除栏目【{$title}】成功！");
            }
            webCacheRm('fupart');
            $this->success('批量删除成功!');
        }
        $this->error("访问错误！");
    }
    //  更改显示状态
    public function see(){
        if(request()->isPost()){
            $id = input('cxbsid');
            $cxmodel = new cxModel();
            $status = $cxmodel->where('id',$id)->find();
            if($status['status'] == '1'){
                if($status['class'] == '0'){
                    $dondata = $cxmodel->sort('donLevel',$status);//获取下级栏目
                    if(!empty($dondata) || isset($dondata)){
                        foreach ($dondata as $val){
                            $cxmodel->where('id',$val['id'])->update(['status' => '0']);
                        }
                    }
                }
                $cxmodel->where('id',$status['id'])->update(['status' => '0']);
                $this->addlog("禁用【{$status['title']}】栏目！");
                webCacheRm('fupart');
                $this->success("禁用【{$status['title']}】栏目！");
            }else{
                $upstatus = $cxmodel->where('id',$status['pid'])->find();
                if(!empty($upstatus) && isset($upstatus) && $upstatus['status'] == '0'){
                    $this->error("请先启用上级分类");
                }
                if($status['class'] == '0'){
                    $dondata = $cxmodel->sort('donLevel',$status);//获取下级栏目
                    if(!empty($dondata) || isset($dondata)){
                        foreach ($dondata as $val){
                            $cxmodel->where('id',$val['id'])->update(['status' => '1']);
                        }
                    }
                }
                $cxmodel->where('id',$status['id'])->update(['status' => '1']);
                $this->addlog("启用【{$status['title']}】栏目！");
                webCacheRm('fupart');
                $this->success("启用【{$status['title']}】栏目！");
            }
        }
        $this->error('访问错误！');
    }
    //  处理删除信息
    protected function delclck($data){
        if(!empty($data['logo'])){
            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$data['logo']);
        }
        return true;
    }


}