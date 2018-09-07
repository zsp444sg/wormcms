<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-29
 * Time: 15:11
 */
namespace app\common\controller;

use think\Controller;
use think\Image;

class Upload extends Controller{
    public $UpDir;
    public $LinshiDir;
    public function _initialize(){
        if(webCacheHas('webdb')){
            $webdb = webCacheGet('webdb');
        }else{
            $webdb = webdb();
        }
        $this->UpDir = $webdb['updir'].'/';
        $this->LinshiDir = $this->UpDir.'linshi/'.session('userdb.uid').'/';
        if (!file_exists($this->UpDir)) {
            @mkdir($this->UpDir);
        }
        if (!file_exists($this->LinshiDir)) {
            @mkdir($this->LinshiDir);
        }
    }
    //  上传文件
    public function upload($data){
        $saveName = $this->newname($data);
        $info = $data->move(ROOT_PATH . $this->LinshiDir, $saveName);
        if($info){
            $imgs = ([
                'title' => $info->getInfo('name'),
                'size' => $info->getInfo('size'),
                'name' => $this->LinshiDir.$info->getSaveName()
            ]);
            return $imgs;
        }
        return false;
    }
    //  处理文章中的图片
    public function articleimg($data,$imgurl,$geturl = true){
        $imgps = "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
        preg_match_all($pattern = $imgps, $data['oldimg'], $oldimg);//获得老的图片
        preg_match_all($pattern = $imgps, $data['newimg'], $newimg);//获得新的图片
        $oldimg = $oldimg[1];
        $newimg = $newimg[1];
        $newimg = array_flip($newimg);
        $newimg = array_flip($newimg);
        if(!empty($oldimg)){
            foreach ($oldimg as $v){
                $oldimgname[] = basename($v);
            }
        }
        //  处理新图片
        if(!empty($newimg)){
            foreach ($newimg as $k => $v){
                if(!empty($oldimgname)){
                    if(in_array(basename($v),$oldimgname)){
                        unset($newimg[$k]);
                    }
                }
                $newimgname[] = basename($v);
            }
        }
        //  处理老图片
        if(!empty($oldimg)){
            foreach ($oldimg as $k => $v){
                if(!empty($newimgname)){
                    if(in_array(basename($v),$newimgname)){
                        unset($oldimg[$k]);
                    }
                }
            }
        }
        //  开始移动
        if(!empty($newimg)){
            if(!file_exists($this->UpDir.$imgurl)){
                @mkdir($this->UpDir.$imgurl);
            }
            if($geturl == true){
                foreach ($newimg as $k => $v){
                    if(preg_match('/http:\/\/*\??[\w=&\+\%]*/is',$v) || preg_match('/https:\/\/*\??[\w=&\+\%]*/is',$v)){
                        $newname = $this->range($v,$imgurl);
                    }elseif(preg_match('/^(data:\s*image\/(\w+);base64,)/',$v,$type)){
                        $newname = $this->base64($v,$type,$imgurl);
                    }elseif(preg_match("/{$this->UpDir}",$v)){
                        $newname = $this->fileMove($v,$imgurl);
                    }
                    if(isset($newname)){
                        $data['newimg'] = str_ireplace($v,'/'.$this->UpDir.$newname,$data['newimg']);
                    }
                }
            }else{
                foreach ($newimg as $k => $v){
                    if(preg_match("/{$this->UpDir}",$v)){
                        $newname = $this->fileMove($v,$imgurl);
                    }
                    if(isset($newname)){
                        $data['newimg'] = str_ireplace($v,'/'.$this->UpDir.$newname,$data['newimg']);
                    }
                }
            }
        }
        if(!empty($oldimg)){
            foreach ($oldimg as $v){
                @unlink(ROOT_PATH.$this->UpDir.'/'.$v);
            }
        }
        return $data['newimg'];
    }
    /**
     * @param $data 要处理的数据
     * @param $file_dir 目标地址
     * @param string $moves 处理方式：copy为复制，rename为移动
     * @param bool $dels 是否删除源文件
     * @return string 返回文件地址
     */
    public function fileMove($data,$file_dir,$moves = 'rename',$dels=true){
        if(is_array($data)){
            foreach ($data as $key => $val){
                $newdata[$key] = $this->fileMove($val,$file_dir,$moves,$dels);
            }
            return $newdata;
        }
        /*重命名*/
        $saveName = $this->newname($data);
        $fileFix = pathinfo($data, PATHINFO_EXTENSION);
        $fileName = $saveName.'.'.$fileFix;
        /*  检测文件夹是否存在 */
        if (!file_exists($this->UpDir.$file_dir)) {
            @mkdir($this->UpDir.$file_dir);
        }
        //       检查文件路径
        if(preg_match("/{$this->UpDir}",$data)){
            $data = str_ireplace("/{$this->UpDir}",$this->UpDir,$data);
        }
        /*移动文件*/
        if($moves == 'rename'){
            @rename($data,ROOT_PATH.$this->UpDir.$file_dir.'/'.$fileName);
        }else{
            @copy($data,ROOT_PATH.$this->UpDir.$file_dir.'/'.$fileName);
        }
        if($dels && $moves != 'rename'){
            @unlink(ROOT_PATH.$data);
        }
        return $file_dir.'/'.$fileName;
    }

    protected function newname($data){
        $imgmd5 = cxbsmd5($data.substr(md5(time()),0,8));
        $saveName = '';
        for ( $i = 0; $i < 10; $i++ ) {
            $saveName .= substr($imgmd5, mt_rand(0, strlen($imgmd5) - 1), 1);
        }
        return $saveName;
    }
    //  获取远程图片
    protected function range($img,$imgurl){
        $mimes = array(
            'image/bmp'=>'bmp',
            'image/gif'=>'gif',
            'image/jpeg'=>'jpg',
            'image/png'=>'png',
            'image/x-icon'=>'ico'
        );
        if(($headers=get_headers($img, 1))!==false) {
            // 获取响应的类型
            $type = $headers['Content-Type'];
            // 如果符合类型
            if (isset($mimes[$type])) {
                $imgmimes = $mimes[$type];
                $newname = $this->newname($img);
                $newname = $imgurl.'/'.$newname.'.'.$imgmimes;
                // 获取数据并保存
                file_put_contents($this->UpDir.'/'.$newname, file_get_contents($img));
                return $newname;
            }
        }
        return $img;
    }
    public function base64($img,$type,$imgurl){
        $imgpng = $type[2];
        $newname = $this->newname($img);
        $newname = $imgurl.'/'.$newname.'.'.$imgpng;
        file_put_contents($this->UpDir.'/'.$newname, file_get_contents($img));
        return $newname;
    }
    //  处理缩略图
    public function pspicurl($img,$imgurl,$dels = true){
        if(preg_match('/http:\/\/*\??[\w=&\+\%]*/is',$img) || preg_match('/https:\/\/*\??[\w=&\+\%]*/is',$img)){
            $img = $this->UpDir.$this->range($img,$imgurl);
            $dels = true;
        }elseif(preg_match('/^(data:\s*image\/(\w+);base64,)/',$img,$type)){
            $img = $this->UpDir.$this->base64($img,$type,$imgurl);
            $dels = true;
        }
        $imgs = Image::open(ROOT_PATH.$img);
        $width = $imgs->width();
        $height = $imgs->height();
        $fileFix = pathinfo($img, PATHINFO_EXTENSION);
        $saveName =  $this->newname($img).'.'.$fileFix;
        if(!is_dir(ROOT_PATH.$this->UpDir.$imgurl)){
            @mkdir(ROOT_PATH.$this->UpDir.$imgurl);
        }
        $imgtu = array(
            '1' => array('w'=>'600','h'=>'600','q'=>'/1x1_'),
            '2' => array('w'=>'600','h'=>'450','q'=>'/4x3_'),
            '3' => array('w'=>'450','h'=>'600','q'=>'/3x4_'),
        );
        foreach ($imgtu as $k => $v){
            $this->thumbs($img,$v,$imgurl,$saveName);
        }
        $imgs->thumb($width,$height,Image::THUMB_FILLED)->save(ROOT_PATH.$this->UpDir.$imgurl.'/'.$saveName);
        $picurl = $imgurl.'/'.$saveName;
        unset($saveName);
        if($dels == true){
            @unlink($img);
        }
        return $picurl;
    }
//    生成缩略图
    public function thumbs($img,$data,$imgurl,$saveName){
        $imgs = Image::open(ROOT_PATH.$img);
        $imgs->thumb($data['w'], $data['h'],2)->save(ROOT_PATH.$this->UpDir.$imgurl.$data['q'].$saveName);
    }
    /**
     * @param $data 删除的文件内容
     * @return bool 返回结果
     */
    public function fileDel($data){
        if(is_array($data)){
            foreach ($data as $key => $val){
                $this->fileDel($val);
            }
            return true;
        }
        if(is_file(ROOT_PATH.$this->UpDir.$data)){
            @unlink(ROOT_PATH.$this->UpDir.$data);
        }
        return true;
    }
    //  隐藏真实地址，为防止更换网址出故障
    public function editadd($data,$adds = true){
        if($adds == true){
            $data = str_replace("/{$this->UpDir}","http://www_cxbs_net/Ls_dir/",$data);
        }else{
            $data = str_replace("http://www_cxbs_net/Ls_dir/","/{$this->UpDir}",$data);
        }
        return $data;
    }

}