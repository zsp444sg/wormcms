<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 11:43
 */
namespace app\home\controller;

use app\common\controller\Indexbase;
use app\common\model\ArtForms;
use app\common\model\Forms as cxModel;
use app\common\controller\Upload;
use think\Loader;

class Forms extends Indexbase {
    public function addform(){
        $cxmodel = new cxModel();
        if(request()->isPost()){
            $artforms = new ArtForms();
            $data = input('post.');
            $data = datatrim($data);
            $formconf = $artforms->formconf($data['fid']);
            if($formconf['tourist'] != '1'){
                $this->error("请先登录",'Login/login');
            }
            $validate = Loader::validate('Forms');
            if (!$validate->scene('add')->check($data)) {
                $this->error($validate->getError());
            }
            $data['addtime'] = time();
            if(db('forms_cont_'.$data['fid'])->insert($data)){
                $this->success("{$formconf['title']}提交成功！");
            }else{
                $this->error("{$formconf['title']}提交失败！");
            }
        }
    }
    //  短信验证
    public function add(){
        if(request()->isPost()){
            $smsmodel = new \app\common\model\Smscode();
            $data = input('post.');
            $data = datatrim($data);
            $code = webCacheGet('lishicode'.$data['phone']);
            if($code != $data['phonecode']){
                $this->error("验证码错误");
            }
            $fid =  $data['fid'];
            $data = $this->modelfields($data);
            $data['addtime'] = time();
            $data['addip'] = request()->ip();
            if(!empty($this->cxbsuser['uid']) && isset($this->cxbsuser['uid'])){
                $data['uid'] = $this->cxbsuser['uid'];
            }
            if(!empty($this->cxbsuser['username']) && isset($this->cxbsuser['username'])){
                $data['username'] = $this->cxbsuser['username'];
            }
            if(db('forms_cont_'.$fid)->insert($data)){
                $smscode = array(
                    'phones' => $this->webdb['adminphone'],
                    'code' => $data['phone'],
                    'tpcode' => $this->webdb['aliyunpaycode'],
                    'title' => '用户提交信息成功'
                );
                $code = $smsmodel->smscode($smscode);
                $smsmodel->where('code',$code)->value('status');
                webCacheRm('lishicode'.$data['phone']);
                webCacheRm('lishicode'.$smscode['phones']);
                $this->success("提交成功，我们将尽快与您联系！");
            }else{
                $this->error("提交失败，请重试！");
            }
        }
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


}