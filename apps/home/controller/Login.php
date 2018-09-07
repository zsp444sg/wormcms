<?php
namespace app\home\controller;

use app\common\controller\Indexbase;
use think\Loader;

class Login extends Indexbase {
    //  新用户注册
    public function reg(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('User');
            if (!$validate->scene('reg')->check($data)) {
                $this->error($validate->getError());
            }
            $code = webCacheGet('lishicode'.$data['uphone']);
            if($code != $data['uncode']){
                $this->error("验证码错误");
            }
            $cxmodel = new \app\common\model\User();
            $userdata = $cxmodel->adduser($data);
            $smsmodel = new \app\common\model\Sms();
            $smsmodel->addsms($userdata);
            webCacheRm('lishicode'.$data['uphone']);
            $this->success("注册成功！");
        }
        $webs = array(
            'title' => '新用户注册'.$this->webdb['webname'],
            'keywords' => $this->webdb['webkeywords'],
            'description' => $this->webdb['description'],
        );
        $this->assign([
            'webs' => $webs,
        ]);
        return view($this->temp.'reg.htm');
    }
    //  用户登录
    public function login(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('User');
            if(!$validate->scene('login')->check($data)){
                $this->error($validate->getError());
            }
            $usermodel = new \app\common\model\User();
            $num = $usermodel->where('username',$data['username'])->count();
            if($num == '0'){
                $this->error("用户不存在");
            }
            $login = $usermodel->loginuser($data);
            if($login == false){
                $this->error("用户名或密码错误");
            }else{
                $this->logins($data['username'],'网页登录');
                $this->success("登录成功");
            }
        }
        if(session('userdb')){
            $this->error("您已登录，请不要重复登录");
        }
        $webs = array(
            'title' => '用户登录'.$this->webdb['webname'],
            'keywords' => $this->webdb['webkeywords'],
            'description' => $this->webdb['description'],
        );
        $this->assign([
            'webs' => $webs,
        ]);
        return view($this->temp.'login.htm');
    }
    public function repwd(){
        if(request()->isPost()) {
            $data = input('post.');
            $data = datatrim($data);
            $validate = Loader::validate('User');
            if(!$validate->scene('repwd')->check($data)){
                $this->error($validate->getError());
            }
            $code = webCacheGet('lishicode'.$data['uphone']);
            if($code != $data['uncode']){
                $this->error("验证码错误");
            }
            $cxmodel = new \app\common\model\User();
            $data['username'] = $data['uphone'];
            $cxmodel->repwd($data);
            webCacheRm('lishicode'.$data['uphone']);
            userOperation($data['username'],'修改密码成功！');
            $this->success("密码修改成功");
        }
        $webs = array(
            'title' => '找回密码'.$this->webdb['webname'],
            'keywords' => $this->webdb['webkeywords'],
            'description' => $this->webdb['description'],
        );
        $this->assign([
            'webs' => $webs,
        ]);
        return view($this->temp.'repwd.htm');
    }
    //  更新登录文件
    public function logins($username,$upassword){
        $logs = model('UserLogin');
        $data = ([
            'username' => $username,
            'password' => $upassword,
        ]);
        $logs->save($data);
    }
    public function loginqu(){
        loginout(session('userdb.uid'));
        $this->success("退出成功！");
    }
}
