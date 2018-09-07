<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-11
 * Time: 8:06
 */
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\admin\model\Article as cxModel;
use app\common\controller\Upload;
use think\Loader;

class Article extends Adminbase {

    protected $beforeActionList = [
        'btnyz'  =>  ['only'=>'index,so,add,edit,huishou'],
    ];
    protected function btnyz(){
        $btn = $this->btnauth('add,edit,del,status');
        $this->assign('btn',$btn);
    }
    //  获取基本信息
    protected function basearr($data = ''){
        $only = $this->webdb['bindingmodel'];
        $basearr = '';
        $basearr['keys'] = model('Keywords')->artkeyword(20);
        $basearr['sources'] = model('Source')->artkeyword(20);
        $basearr['groups'] = model('AuthGroup')->grouplst();
        $basearr['fids'] = model('Part')->artlist($data,$only);
        $basearr['fuids'] = model('FuPart')->artfulist(isset($data)?$data:'');
        $basearr['sids'] = model('Special')->artspecial(isset($data)?$data:'');
        return $basearr;
    }
    //  列表
    public function index(){
        if(request()->param()){
            $getdata = request()->param();
            $getdata = datatrim($getdata);
        }
        if(empty($getdata['mid']) || !isset($getdata['mid'])){
            $getdata['mid'] = '0';
        }
        if(!empty($getdata['keyword']) && isset($getdata['keyword'])){
            $getdata['title|mtitle|keywords|description|username|author|source'] = array('like',"%".$getdata['keyword']."%");
            unset($getdata['keyword']);
        }
        if(empty($getdata['status']) || !isset($getdata['status'])){
            $getdata['status'] = array('<>','2');
        }
        unset($getdata['page']);
        $cxmodel = new cxModel();
        if(!empty($getdata['fuid']) || isset($getdata['fuid'])){
            $list = $cxmodel->where('','EXP',"FIND_IN_SET({$getdata['fuid']},fuid)")->where('status','1')->order('top desc,jian desc,sort desc,aid desc')->paginate('20',false,['query' => request()->param()])->each(function ($item){
                $item->parttitle = model('Part')->where('id',$item->fid)->value('title');
                $item->page = model('ArtCont')->where('aid',$item->aid)->count();
            });
        }else{
            $list = $cxmodel->where($getdata)->order('top desc,jian desc,sort desc,aid desc')->paginate('20',false,['query' => request()->param()])->each(function ($item){
                $item->parttitle = model('Part')->where('id',$item->fid)->value('title');
                $item->page = model('ArtCont')->where('aid',$item->aid)->count();
            });
        }
        $this->assign([
            'list' => $list,
        ]);
        return view();
    }
    //  回收站
    public function huishou(){
        $cxmodel = new cxModel();
        $list = $cxmodel->where('status','2')->order('top desc,jian desc,sort desc,aid desc')->paginate('20')->each(function ($item){
            $item->parttitle = model('Part')->where('id',$item->fid)->value('title');
            $item->page = model('ArtCont')->where('aid',$item->aid)->count();
        });
        $this->assign([
            'list' => $list,
        ]);
        return view();
    }
    //  添加内容
    public function add(){
        $getdata = request()->param();
        if(empty($getdata['mid']) || !isset($getdata['mid'])){
            $getdata['mid'] = '0';
        }
        if($getdata['mid'] == '0'){
            $views = 'add';
        }else{
            $artmodel = model('ArtModel')->artmodel();
            foreach ($artmodel as $key => $val){
                if($val['id'] == $getdata['mid']){
                    $artmodel = $val;
                    break;
                }
            }
            $artmodel = unserialize($artmodel['config']);
            $artmodel = $artmodel['model_db'];
            $views = "data/post/post_{$getdata['mid']}.htm";
            if(!isset($views)){
                $this->error("模板不存在，请先生成模板！");
            }
            $this->assign('modelbase',$artmodel);
        }
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Article');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['sid'])){
                if(!$validate->scene('num')->check($data['sid'])){
                    $this->error('专题选择错误！');
                }
            }
            if(!empty($data['fuid'])){
                if(!$validate->scene('num')->check($data['fuid'])){
                    $this->error('辅助栏目选择错误！');
                }
            }
            if(!empty($data['seegroup'])){
                if(!$validate->scene('num')->check($data['seegroup'])){
                    $this->error('允许查看内容的用户组选择错误！');
                }
            }
            if(!empty($data['dontgroup'])){
                if(!$validate->scene('num')->check($data['dontgroup'])){
                    $this->error('允许下载的用户组选择错误！');
                }
            }
            //  开始处理数据
            $data = $cxmodel->addbase($data);
            $data['uid'] = $this->cxbsuser['uid'];
            $data['username'] = $this->cxbsuser['username'];
            if($data['mid'] != 0){
                $content = $this->modelfields($data,null);
            }
            if(!empty($data['picurl']) && isset($data['picurl'])){
                $data = $this->picurl($data);
            }
            $data = $this->addedit($data);
            if(!empty($data['fuid']) && isset($data['fuid'])){
                $fuid = explode(',',$data['fuid']);
                foreach ($fuid as $k => $v){
                    unset($list);
                    $list = array(
                        'aid' => $data['aid'],
                        'fuid' => $v
                    );
                    model('FuArticle')->isUpdate(false)->save($list);
                }
            }
            if($cxmodel->allowField(true)->save($data)){
                $content['aid'] = $data['aid'] = $cxmodel->aid;
                model('ArtCont')->allowField(true)->save($data);
                if($data['mid'] != 0){
                    db("article_content_{$data['mid']}")->insert($content);
                }

                $this->addlog("发布内容{$data['title']}成功！");
                $this->success('发布内容成功！',url('index',array('mid'=>$data['mid'],'fid'=>$data['fid'])));
            }else{
                $this->editadddel($data);
                $this->error('发布失败！');
            }
        }
        $basearr = $this->basearr($getdata);
        $this->assign([
            'basearr' => $basearr,
        ]);
        return view($views);
    }
    //  编辑内容
    public function edit(){
        if(!request()->param('aid')){
            $this->error('访问错误！');
        }
        $getdata = request()->param();
        $cxmodel = new cxModel();
        $upload = new Upload();
        $edit = $cxmodel->where('aid',$getdata['aid'])->find();
        $edit['content'] = model('ArtCont')->where('aid',$edit['aid'])->value('content');
        if(!empty($edit['content'])){
            $edit['content'] = $upload->editadd($edit['content'],false);
        }
        $edit['template'] = unserialize($edit['template']);
        if($edit['mid'] == '0'){
            $views = 'add';
        }else{
            $artmodel = model('ArtModel')->artmodel();
            foreach ($artmodel as $key => $val){
                if($val['id'] == $edit['mid']){
                    $artmodel = $val;
                    break;
                }
            }
            $artmodel = unserialize($artmodel['config']);
            $artmodel = $artmodel['model_db'];
            $edit = $this->usmodelfields($edit);
            $views = "data/post/post_{$getdata['mid']}.htm";
            if(!isset($views)){
                $this->error("模板不存在，请先生成模板！");
            }
            $this->assign('modelbase',$artmodel);
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('Article');
            if(!$validate->scene('add')->check($data)){
                $this->error($validate->getError());
            }
            if(!empty($data['sid'])){
                if(!$validate->scene('num')->check($data['sid'])){
                    $this->error('专题选择错误！');
                }
            }
            if(!empty($data['fuid'])){
                if(!$validate->scene('num')->check($data['fuid'])){
                    $this->error('辅助栏目选择错误！');
                }
            }
            if(!empty($data['seegroup'])){
                if(!$validate->scene('num')->check($data['seegroup'])){
                    $this->error('允许查看内容的用户组选择错误！');
                }
            }
            if(!empty($data['dontgroup'])){
                if(!$validate->scene('num')->check($data['dontgroup'])){
                    $this->error('允许下载的用户组选择错误！');
                }
            }
            //  开始处理数据
            $data = $cxmodel->addbase($data);
            if($data['mid'] != 0){
                $content = $this->modelfields($data,$edit);
            }
            if($data['picurl'] != $edit['picurl'] || empty($data['picurl'])) {
                $data = $this->picurl($data);
            }
            $data = $this->addedit($data,$edit['content']);
            if(!empty($data['fuid']) && isset($data['fuid'])){
                if($edit['fuid'] != $data['fuid']){
                    model('FuArticle')->where('aid',$data['aid'])->delete();
                    $fuid = explode(',',$data['fuid']);
                    foreach ($fuid as $k => $v){
                        unset($list);
                        $list = array(
                            'aid' => $data['aid'],
                            'fuid' => $v
                        );
                        model('FuArticle')->isUpdate(false)->save($list);
                    }
                }
            }
            if($cxmodel->allowField(true)->save($data,$data['aid'])){
                $data['id'] = model('ArtCont')->where('aid',$data['aid'])->value('id');
                model('ArtCont')->allowField(true)->save($data,$data['id']);
                //  判断缩略图是否一致
                if($data['picurl'] != $edit['picurl']){
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$edit['picurl']);
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($edit['picurl']),'1x1_'.basename($edit['picurl']),$edit['picurl']));
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($edit['picurl']),'3x4_'.basename($edit['picurl']),$edit['picurl']));
                    @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($edit['picurl']),'4x3_'.basename($edit['picurl']),$edit['picurl']));
                }
                if($data['mid'] != 0){
                    unset($data['id']);
                    db("article_content_{$data['mid']}")->where('aid',$data['aid'])->update($content);
                }
                $this->addlog("修改内容{$data['title']}成功！");
                $this->success('修改内容成功！',url('index',array('mid'=>$data['mid'],'fid'=>$data['fid'])));
            }else{
                $this->editadddel($data);
                $this->error('修改失败！');
            }
        }
        $basearr = $this->basearr($edit);
        $this->assign([
            'basearr' => $basearr,
            'postdb' => $edit,
        ]);
        return view($views);
    }
    //  修改审核状态
    public function see(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['status'] == 1){
                cxModel::update(['aid' => $see['aid'],'status' => '0']);
                $this->addlog('取消审核内容【'.$see['title'].'】！');
                $this->success("取消审核内容【{$see['title']}】！");
            }elseif($see['status'] == 0){
                cxModel::update(['aid' => $see['aid'],'status' => '1']);
                $this->addlog('通过审核【'.$see['title'].'】！');
                $this->success("通过审核【{$see['title']}】！");
            }else{
                cxModel::update(['aid' => $see['aid'],'status' => '1']);
                $this->addlog('还原【'.$see['title'].'】！');
                $this->success("还原【{$see['title']}】！");
            }
        }
        $this->error('访问错误！');
    }
    //  修改推荐状态
    public function jian(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['jian'] == 1){
                cxModel::update(['aid' => $see['aid'],'jian' => '0']);
                $this->addlog('取消推荐【'.$see['title'].'】！');
                $this->success("取消推荐【{$see['title']}】！");
            }elseif($see['jian'] == 0){
                cxModel::update(['aid' => $see['aid'],'jian' => '1']);
                $this->addlog('推荐成功【'.$see['title'].'】！');
                $this->success("推荐成功【{$see['title']}】！");
            }
        }
        $this->error('访问错误！');
    }
    //  修改置顶状态
    public function top(){
        if(request()->isPost()){
            $data = input('post.');
            $see = cxModel::get((int)$data['cxbsid']);
            if($see['top'] == 1){
                cxModel::update(['aid' => $see['aid'],'top' => '0']);
                $this->addlog('取消推荐【'.$see['title'].'】！');
                $this->success("取消推荐【{$see['title']}】！");
            }elseif($see['top'] == 0){
                cxModel::update(['aid' => $see['aid'],'top' => '1']);
                $this->addlog('推荐成功【'.$see['title'].'】！');
                $this->success("推荐成功【{$see['title']}】！");
            }
        }
        $this->error('访问错误！');
    }
    //  排序
    public function sort(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new cxModel();
            foreach ($data['sort'] as $k => $v){
                $list[] = array(
                    'aid' => $k,
                    'sort' => $v
                );
            }
            $cxmodel->saveAll($list);
            $this->addlog('更改排序成功！');
            $this->success('更改排序成功!');
        }
        $this->error("访问错误！");
    }
    //  批量删除至回收站
    public function edel(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new cxModel();
            foreach ($data['pdel'] as $k => $v){
                $list[] = array(
                    'aid' => $v,
                    'status' => '2'
                );
            }
            $cxmodel->saveAll($list);
            $this->addlog('删除至回收站成功！');
            $this->success('删除至回收站成功!');
        }
        $this->error("访问错误！");
    }
    //  批量从回收站还原
    public function esee(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new cxModel();
            foreach ($data['pdel'] as $k => $v){
                $list[] = array(
                    'aid' => $v,
                    'status' => '1'
                );
            }
            $cxmodel->saveAll($list);
            $this->addlog('从回收站还原成功！');
            $this->success('从回收站还原成功!');
        }
        $this->error("访问错误！");
    }
    //  删除文章
    public function del(){
        if(!request()->param()){
            $this->error('选择错误！');
        }
        if(request()->isPost()){
            $del = input('post.');
            $upmodel = new Upload();
            $deldata = cxModel::get($del['id']);
            if($deldata['status'] !== 2){
                cxModel::update(['aid' => $deldata['aid'],'status' => '2']);
                $this->success('已删除至回收站！');
            }
            $deldata['content'] = model('ArtCont')->where('aid',$deldata['aid'])->value('content');
            if(isset($deldata['content'])){
                $deldata['content'] = $upmodel->editadd($deldata['content'],false);
            }
            $title = $deldata['title'];
            $this->delclck($deldata);
            if($deldata['mid'] != '0'){
                $content = $this->usmodelfields($deldata);
                $this->delmodelfields($content);
                db('article_content_'.$deldata['mid'])->where('aid',$deldata['aid'])->delete();;
            }
            model('ArtCont')->where('aid',$deldata['aid'])->delete();
            $deldata->delete();
            $this->addlog("删除内容【{$title}】");
            $this->success("删除内容【{$title}】");
        }
        $this->error('访问错误！');
    }
    //  批量删除
    public function pdel(){
        if(!request()->param()){
            $this->error('选择错误！');
        }
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $upmodel = new Upload();
            foreach ($data['pdel'] as $k => $v){
                $deldata = cxModel::get($v);
                $deldata['content'] = model('ArtCont')->where('aid',$deldata['aid'])->value('content');
                if(isset($deldata['content'])){
                    $deldata['content'] = $upmodel->editadd($deldata['content'],false);
                }
                $title = $deldata['title'];
                $this->delclck($deldata);
                if($deldata['mid'] != '0'){
                    $content = $this->usmodelfields($deldata);
                    $this->delmodelfields($content);
                    db('article_content_'.$deldata['mid'])->where('aid',$deldata['aid'])->delete();;
                }
                model('ArtCont')->where('aid',$deldata['aid'])->delete();
                $deldata->delete();
                $this->addlog("删除内容【{$title}】");
            }
            $this->success("批量删除成功！");
        }
        $this->error("选择错误！");
    }
    //  处理关键词与图片
    protected function addedit($data,$editdata = ''){
        $upmodel = new Upload();
        //  处理关键词
        if(!empty($data['keywords'])){
            $data['keywords'] = str_replace(array(' ','，'),',',$data['keywords']);
            $keyword = explode(',',$data['keywords']);
            model('Keywords')->addkeyword($keyword,$this->cxbsuser['uid']);
        }
        //  处理来源
        if(!empty($data['sourcerk']) && $data['sourcerk'] == 1){
            $source['title'] = $data['source'];
            $source['url'] = $data['sourceurl'];
            model('Source')->addsource($source,$this->cxbsuser['uid']);
        }
        // 检测是否需要取回图片
        $pirdir = date("Ym");
        $imgmove = array(
            'oldimg' => $editdata,
            'newimg' => $data['content'],
        );
        //  处理图片
        if(!empty($data['getpic']) && $data['getpic'] == '1'){
            $data['content'] = $upmodel->articleimg($imgmove,$pirdir);
        }else{
            $data['content'] = $upmodel->articleimg($imgmove,$pirdir,$geturl = false);
        }
        //  处理内容  检测是否需要去除链接
        if(!empty($data['geturl']) && $data['geturl'] == '1'){
            $data['content'] = preg_replace('/<a [^>]*>|<\/a>/','',$data['content']);
        }
        if(empty($data['picurl']) || !isset($data['picurl'])){
            $data = $this->picurl($data);
        }
        $data['content'] = $upmodel->editadd($data['content']);
        return $data;
    }
    //  生成缩略图
    protected function picurl($data){
        $upmodel = new Upload();
        $pirdir = date("Ym");
        // 生成缩略图
        if(!empty($data['picurl']) && isset($data['picurl'])){
            $data['picurl'] = $upmodel->pspicurl($data['picurl'],$pirdir);
            $data['toppic'] = 1;
        }else{
            if($this->webdb['onepic'] == '1'){
                preg_match ("/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/",$data['content'],$match);
                if(!empty($match) && isset($match)){
                    $data['picurl'] = $upmodel->pspicurl($match[1],$pirdir,false);
                    $data['toppic'] = 1;
                }
            }else{
                $data['toppic'] = 0;
            }
        }
        return $data;
    }
    //  删除附加表及内容表
    protected function delclck($data){
        //  检测描述中的图片
        if($data['content']){
            $imgps = "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
            preg_match_all($pattern = $imgps, $data['content'], $donimg);//获得老的图片
            if(!empty($donimg)){
                $delimg = array_flip($donimg[1]);
                $delimg = array_flip($delimg);
                foreach ($delimg as $v){
                    @unlink(ROOT_PATH.$v);
                }
            }
        }
        //  处理logo
        if($data['picurl']){
            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.$data['picurl']);
            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($data['picurl']),'1x1_'.basename($data['picurl']),$data['picurl']));
            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($data['picurl']),'3x4_'.basename($data['picurl']),$data['picurl']));
            @unlink(ROOT_PATH.$this->webdb['updir'].'/'.str_replace(basename($data['picurl']),'4x3_'.basename($data['picurl']),$data['picurl']));
        }
        return true;
    }
    //  处理模型字段
    protected function modelfields($data,$olddata=''){
        $cxmodel = new cxModel();
        if(webCacheHas('artmodel'.$data['mid'])){
            $model = webCacheGet('artmodel'.$data['mid']);
        }else{
            $model = model('artModel')->artmodel();
            foreach ($model as $val){
                if($val['id'] == $data['mid']){
                    $model = $val;
                    webCacheSet('artmodle'.$data['mid'],$model);
                    break;
                }
            }
        }
        //  定义基本属性
        $model = unserialize($model['config']);
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
        $cont = $cxmodel->futabel($data['mid']);
        $rescont = null;
        foreach ($cont as $k => $v){
            if(!isset($data[$v])){
                continue;
            }
            $rescont[$v] = $data[$v];
        }
        return $rescont;
    }
    //  删除附加表中的内容
    public function delmodelfields($data){
        if(webCacheHas('artmodel'.$data['mid'])){
            $model = webCacheGet('artmodel'.$data['mid']);
        }else{
            $model = model('artModel')->artmodel();
            foreach ($model as $val){
                if($val['id'] == $data['mid']){
                    $model = $val;
                    webCacheSet('artmodle'.$data['mid'],$model);
                    break;
                }
            }
        }
        $model = unserialize($model['config']);
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
    //  对附加表进行读取解释
    public function usmodelfields($data){
        if(webCacheHas('artmodel'.$data['mid'])){
            $model = webCacheGet('artmodel'.$data['mid']);
        }else{
            $model = model('artModel')->artmodel();
            foreach ($model as $val){
                if($val['id'] == $data['mid']){
                    $model = $val;
                    webCacheSet('artmodle'.$data['mid'],$model);
                    break;
                }
            }
        }
        $upmodel = new Upload();
        $model = unserialize($model['config']);
        $fielddb = $model['field_db'];
        $edits = db("article_content_{$data['mid']}")->where('aid',$data['aid'])->find();
        foreach ($fielddb as $k => $v){
            if(!isset($edits[$k]) || empty($edits[$k]) || $edits[$k] == null){
                continue;
            }
            if($v['formtype'] == 'textarea'){
                $edits[$k] = preg_replace('/<br>/',"\r",$edits[$k]);
            }elseif($v['formtype'] == 'uptxt' || $v['formtype'] == 'upimg' || $v['formtype'] == 'upmv'){
                $edits[$k] = explode('&@&@&',$edits[$k]);
                $arr = '';
                foreach ($edits[$k] as $key => $val){
                    $arr[] = explode('@@@',$val);
                }
                $edits[$k] = null;
                foreach ($arr as $value){
                    $arrs['title'] = $value[0];
                    $arrs['value'] = $upmodel->editadd($value[1],false);
                    $edits[$k][] = $arrs;
                }
                unset($arr);
                unset($arrs);
            }elseif($v['formtype'] == 'upfile'){
                $edits[$k] = $upmodel->editadd($edits[$k],false);
            }elseif($v['formtype'] == 'chinacode'){
                $edits[$k] = explode(',',$edits[$k]);
            }
        }
        $edit = array_merge($edits,$data->toArray());
        return $edit;
    }
}