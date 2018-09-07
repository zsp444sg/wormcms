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

use app\common\controller\Base;
use app\common\controller\Cxcache;
use app\common\model\LogsAdlogin;
use think\Cache;
use think\captcha\Captcha;

class Login extends Base {
    public function _initialize(){
        parent::_initialize();
    }
    //  管理员登录
    public function index(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            if($this->webdb['web_adminyz'] == '1'){
                $captcha = new Captcha();
                if(!$captcha->check($data['incode'])){
                    $this->error("验证码错误！");
                }
            }
            $user = model('User')->login($data);
            $data['upassword'] = $data['userpassword'];
            switch ($user){
                case '1':
                    $this->logins($data);
                    $this->error("用户不存在");
                break;
                case '2':
                    $this->logins($data);
                    $this->error("用户已禁用，请联系管理员！");
                break;
                case '3':
                    $this->logins($data);
                    $this->error("用户名或密码错误！");
                break;
            }
            userCacheSet($user['uid'],'cxbsuser',$user);
            addsession('_admin_',$user);
            addsession('userdb',$user);
            $this->logins($user);
            $this->success("登录成功",'Index/index');
        }
        $webdb = webdb();
        $this->assign('webdb',$webdb);
        return view();
    }
    public function yzm(){
        $incode = incode('16','4');
        return $incode;
    }
    public function loginqu(){
        loginout(session('_admin_.uid'));
        return $this->redirect('index');
    }
    /*
        * 登录文件
        */
    protected function logins($data){
        $logins = new LogsAdlogin();
        $data = ([
            'username' => $data['username'],
            'password' => $data['upassword'],
        ]);
        $logins->save($data);
    }
}
