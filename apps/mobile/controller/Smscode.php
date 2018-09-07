<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 11:43
 */
namespace app\mobile\controller;

use app\common\controller\Mobilebase;

class Smscode extends Mobilebase {

    //  短信验证
    public function smscode(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            if($data['type'] == '1'){
                $data['title'] = '注册会员';
                $users = model('User')->where('username',$data['phones'])->find();
                if(isset($users) || !empty($users)){
                    $this->error("用户已存在");
                }
                $data['tpcode'] = $this->webdb['aliyuncode'];
            }
            if($data['type'] == '2'){
                $data['title'] = '下单成功';
                $data['tpcode'] = $this->webdb['aliyunordercode'];
            }
            if($data['type'] == '3'){
                $data['title'] = '交易成功';
                $data['tpcode'] = $this->webdb['aliyunpaycode'];
            }
            if($data['type'] == '4'){
                $users = model('User')->where('username',$data['phones'])->find();
                if(!isset($users) || empty($users)){
                    $this->error("用户不存在");
                }
                $data['title'] = '找回密码';
                $data['tpcode'] = $this->webdb['aliyunpwdcode'];
            }
            $cxmodel = new \app\common\model\Smscode();
            $code = $cxmodel->smscode($data);
            $code = $cxmodel->where('code',$code)->value('status');
            if($code == '0'){
                $this->error("发送失败，请重试");
            }else{
                $this->success("发送成功");
            }
        }
    }



}