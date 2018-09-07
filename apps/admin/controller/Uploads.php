<?php
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\common\controller\Upload;

class Uploads extends Adminbase {
    public function index(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $file = request()->file();
            $data = implode('',$data);
            return $this->$data($file);
        }
        $this->error('访问错误！');
    }
    //  上传单图片
    public function img($data){
        $upmodel = new Upload();
        if(is_array($data) && $data){
            foreach ($data as $v){
                $file = $upmodel->upload($v);
            }
        }
        if($file && is_array($file)){
            $arr['code'] = 0;
            $arr['msg'] = "成功上传";
            $arr['src'] =$file['name'];
            $arr['title'] =$file['title'];
            $arr['size'] =$file['size'];
        }else{
            $arr['code'] = 1;
            $arr['msg'] = "上传失败";
            $arr['src'] =$file['name'];
            $arr['title'] =$file['title'];
        }
        return json_encode($arr);
    }
    //  上传单文件
    public function dfile($data){
        $upmodel = new Upload();
        if(is_array($data) && $data){
            foreach ($data as $v){
                $file = $upmodel->upload($v);
            }
        }
        if($file && is_array($file)){
            $arr['code'] = 0;
            $arr['msg'] = "成功上传";
            $arr['src'] =$file['name'];
            $arr['title'] =$file['title'];
            $arr['size'] =$file['size'];
        }else{
            $arr['code'] = 1;
            $arr['msg'] = "上传失败";
        }
        return json_encode($arr);
    }
    //  编辑器上传图片
    public function wangeditor(){
        if(request()->isPost()){
            $data = input('post.');
            $file = request()->file();
            $upmodel = new Upload();
            foreach ($file as $value){
                $files = $upmodel->upload($value);
                $refiles[] = array('/'.$files['name']);
            }
            $refile = array(
               'errno'=>'0',
               'data' => $refiles
            );
            $refile = json_encode($refile);
            return $refile;
        }
        $this->error('访问错误！');
    }


}
