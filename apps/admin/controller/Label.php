<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\common\model\Label as cxModel;
use app\common\controller\Upload;
use think\Loader;

class Label extends Adminbase {
    //  获取标签参数
    public function index(){
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        if(!empty($getdata['onlay']) && isset($getdata['onlay'])){
            $onlay = $getdata['onlay'];
            unset($getdata['onlay']);
        }
        $cxmodel = new cxModel();
        //  查询数据
        $postdb = $cxmodel->where($getdata)->find();
        //  获取标签的数据
        if(isset($getdata['type']) && $getdata['type'] == 'getnew' && empty($postdb) && !isset($postdb)){
            $cxmodel->allowField(true)->isUpdate(false)->save($getdata);
            $postdb = $cxmodel->where('id',$cxmodel->id)->find();
        }
        $temp = null;
        if(empty($onlay) && !isset($onlay)){
            switch ($postdb['type']){
                case 'htmledit':
                    $postdb['conf'] = unserialize($postdb['conf']);
                    $temp = 'htmledit';
                    break;
                case 'imagesedit':
                    $postdb['conf'] = unserialize($postdb['conf']);
                    $temp = 'imagesedit';
                    break;
                case 'partedit':
                    $postdb['conf'] = unserialize($postdb['conf']);
                    $postdb = $this->parteditbase($postdb);
                    $temp = 'partedit';
                    break;
                case 'parts':
                    $postdb['conf'] = unserialize($postdb['conf']);
                    $postdb = $this->partsbase($postdb);
                    $temp = 'parts';
                    break;
                case 'fupartedit':
                    $postdb['conf'] = unserialize($postdb['conf']);
                    $postdb = $this->fuparteditbase($postdb);
                    $temp = 'fupartedit';
                    break;
            }
        }
        $this->assign('postdb',$postdb);
        return view($temp);
    }
    //  编辑器
    public function htmledit(){
        $cxmodel = new cxModel();
        $upmodel = new Upload();
        $getdata = request()->param();
        $postdb = $cxmodel->find($getdata['id']);
        $postdb['conf'] = unserialize($postdb['conf']);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            // 检测是否需要取回图片
            $pirdir = date("Ym");
            $imgmove = array(
                'oldimg' => $postdb,
                'newimg' => $data['conf'],
            );
            $data['conf'] = $upmodel->editadd($upmodel->articleimg($imgmove,$pirdir));
            $data['type'] = 'htmledit';
            $data['conf'] = serialize($data['conf']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                if(!empty($postdb['conf']['img']) || isset($postdb['conf']['img'])){
                    if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img'])){
                        @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img']);
                    }
                }
                $this->success('编辑成功！',url('htmledit',array('id'=>$data['id'])));
            }else{
                $this->error('编辑失败！');
            }
        }
        if($postdb['type'] != 'htmledit'){
            unset($postdb['conf']);
        }
        $this->assign('postdb',$postdb);
        return view('htmledit');
    }
    //  单张图片
    public function imagesedit(){
        $cxmodel = new cxModel();
        $upmodel = new Upload();
        $getdata = request()->param();
        $postdb = $cxmodel->find($getdata['id']);
        $postdb['conf'] = unserialize($postdb['conf']);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['type'] = 'imagesedit';
            $pirdir = date("Ym");
            if(!empty($data['conf']['img']) && isset($data['conf']['img'])){
                $img = $data['conf']['img'];
                if(!empty($postdb['conf']['img']) && isset($postdb['conf']['img'])){
                    if($data['conf']['img'] != $postdb['conf']['img']){
                        $data['conf']['img'] = $upmodel->fileMove($img,$pirdir);
                        if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img'])){
                            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img']);
                        }
                    }
                }else{
                    $data['conf']['img'] = $upmodel->fileMove($img,$pirdir);
                }
            }else{
                if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img'])){
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img']);
                }
            }
            $data['conf'] = serialize($data['conf']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                $this->success('编辑成功！',url('imagesedit',array('id'=>$data['id'])));
            }else{
                $this->error('编辑失败！');
            }
        }
        if($postdb['type'] != 'imagesedit'){
            $postdb['conf']='';
        }
        $this->assign('postdb',$postdb);
        return view('imagesedit');
    }
    //  调用主栏目
    public function parts(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $postdb = $cxmodel->find($getdata['id']);
        $postdb['conf'] = unserialize($postdb['conf']);
        $postdb = $this->partsbase($postdb);
        //  接收数据
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['type'] = 'parts';
            $data['conf']['fids'] = implode(',',$data['fid']);
            $data['conf'] = serialize($data['conf']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                $this->success('编辑成功！',url('parts',array('id'=>$data['id'])));
            }else{
                $this->error('编辑失败！');
            }
        }
        $this->assign([
            'postdb' => $postdb,
        ]);
        return view('parts');
    }
    //  调用主栏目内容
    public function partedit(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $postdb = $cxmodel->find($getdata['id']);
        $postdb['conf'] = unserialize($postdb['conf']);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['type'] = 'partedit';
            $data['conf']['fids'] = implode(',',$data['fid']);
            $data['conf']['listtqmp'] = implode(',',$data['listtqmp']);
            $data['conf'] = serialize($data['conf']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                if(!empty($postdb['conf']['img']) || isset($postdb['conf']['img'])){
                    if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img'])){
                        @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img']);
                    }
                }
                $this->success('编辑成功！',url('partedit',array('id'=>$data['id'])));
            }else{
                $this->error('编辑失败！');
            }
        }
        if($postdb['type'] != 'partedit'){
            $postdb['conf'] = '';
        }
        $postdb = $this->parteditbase($postdb);
        $this->assign('postdb',$postdb);
        return view('partedit');
    }
    //  调用辅栏目
    public function fupartedit(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $postdb = $cxmodel->find($getdata['id']);
        $postdb['conf'] = unserialize($postdb['conf']);
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['type'] = 'fupartedit';
            $data['conf']['fids'] = implode(',',$data['fid']);
            $data['conf']['listtqmp'] = implode(',',$data['listtqmp']);
            $data['conf'] = serialize($data['conf']);
            if($cxmodel->allowField(true)->isUpdate(true)->save($data,$data['id'])){
                if(!empty($postdb['conf']['img']) || isset($postdb['conf']['img'])){
                    if(is_file(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img'])){
                        @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$postdb['conf']['img']);
                    }
                }
                $this->success('编辑成功！',url('fupartedit',array('id'=>$data['id'])));
            }else{
                $this->error('编辑失败！');
            }
        }
        if($postdb['type'] != 'fupartedit'){
            $postdb['conf'] = '';
        }
        $postdb = $this->fuparteditbase($postdb);
        $this->assign('postdb',$postdb);
        return view('fupartedit');
    }
    //  调用自订义地区
    public function diquedit(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = $this->datatrim($data);
            $validate = Loader::validate('Label');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            $data['type'] = 'diquedit';
            $data['conf']['diqus'] = implode(',',$data['diqus']);
            $data['conf']['temps'] = $data['temps'];
            $data['conf'] = serialize($data['conf']);
            if(isset($data['id']) && $data['id']){
                $cxmodel->allowField(true)->save($data,$data['id']);
            }else{
                $cxmodel->allowField(true)->save($data);
            }
            $this->success('编辑成功！',url('diquedit',array('title'=>$data['title'],'plate'=>$data['plate'])));
        }
        if(request()->isGet()){
            $getdata = request()->param();
        }
        $data = $this->unserze($getdata);
        if(!empty($data) || isset($data)){
            $datas = $this->diquditbase($data);
            if(!empty($datas) && isset($datas)){
                $data = array_merge($datas,$data->toArray());
            }
        }else{
            $data = $this->diquditbase($getdata);
            $data = array_merge($data,$getdata);
        }
        $this->assign('postdb',$data);
        return view('diquedit');
    }
    //  调用主栏目信息
    protected function partsbase($data){
        $cxmodel = new cxModel();
        //  获取所有主栏目
        $partmodel = new \app\common\model\Part();
        $array = $partmodel->allpart();
        foreach ($array as $k => $v){
            if($v['status'] != 1){
                continue;
            }
            $partlist[$k] = $v;
        }
        $fids = null;
        if(!empty($data['conf']['fids']) && isset($data['conf']['fids'])){
            $fids = $data['conf']['fids'];
        }
        $data['dyparts'] = $cxmodel->fuidsbase($partlist,$fids,true);
        return $data;
    }
    protected function diquditbase($data){
        $cxmodel = new cxModel();
        if(empty($data['conf']) || $data['type'] != 'diquedit'){
            $olddata['conf'] = '';
            $olddata['diqus'] = $cxmodel->diqus();
        }else{
            $olddata['diqus'] = $cxmodel->diqus($data['conf']['diqus']);
        }
        return $olddata;
    }
//  调用主栏目内容基本属性
    protected function parteditbase($data){
        $cxmodel = new cxModel();
        $partmodel = new \app\admin\model\Part();
        $artmodel = new \app\admin\model\ArtModel();
        //  获取栏目信息
        $partlist = $partmodel->where('class','<>',2)->where('status','1')->order('sort desc,id asc')->select();
        $partlist = $partmodel->zcolumn($partlist);
        $fids = '';
        if(isset($data['conf']) && !empty($data['conf']['fids']) && isset($data['conf']['fids'])){
            $fids = $data['conf']['fids'];
        }
        //  获取模型信息
        $artmodellist = $artmodel->artmodel();
        foreach ($artmodellist as $key => $val){
            if($val['status'] == '0'){
                unset($artmodellist[$key]);
            }
        }
        $data['dymids'] = $artmodellist;
        $data['dyparts'] = $cxmodel->fuidsbase($partlist,$fids);
        if(empty($data['conf']) || $data['type'] != 'partedit'){
            $data['temps'] = $cxmodel->temps($data['plate']);
        }else{
            $data['temps'] = $cxmodel->temps($data['conf']['listtqmp'],$data['plate']);
        }
        return $data;
    }
//  调用辅助栏目基本属性
    protected function fuparteditbase($data){
        $cxmodel = new cxModel();
        $partmodel = new \app\admin\model\FuPart();
        //  获取栏目信息
        $partlist = $partmodel->where('status','1')->order('sort desc,id asc')->select();
        $partlist = $partmodel->zcolumn($partlist);
        $fids = '';
        if(isset($data['conf']) && !empty($data['conf']['fids']) && isset($data['conf']['fids'])){
            $fids = $data['conf']['fids'];
        }
        $data['dyparts'] = $cxmodel->fuidsbase($partlist,$fids);
        if(empty($data['conf']) || $data['type'] != 'partedit'){
            $data['temps'] = $cxmodel->temps($data['plate']);
        }else{
            $data['temps'] = $cxmodel->temps($data['conf']['listtqmp'],$data['plate']);
        }
        return $data;
    }


}