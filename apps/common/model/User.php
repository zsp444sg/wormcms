<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 11:48
 */
namespace app\common\model;
use think\Model;

class User extends Model{
    // 关联用户资料表
    public function userdb(){
        return $this->hasOne('UserData','uid','uid');
    }
    // 关联用户组表
    public function usergroup(){
        return $this->hasOne('AuthGroupAccess','uid','uid');
    }
    //  用户登录
    public function loginuser($data){
        $user = $this->where('username',$data['username'])->find();
        if(yzpwd($data['upassword'],$user['upassword'])){
            $users = $user->userdb;
            $users = $users->toArray();
            unset($users['upassword']);
            userCacheSet($user['uid'],'userdb',$users);
            session('userdb',$users);
            return true;
        }else{
            return false;
        }
    }
    //  添加新用户，注册页面来的
    public function adduser($data){
        $data['upassword'] = pwd($data['upassword']);
        $newdata = ([
            'group_id' => '5',
            'username' => $data['uphone'],
            'uniname' => $data['uname'],
            'uname' => $data['uname'],
            'uphone' => $data['uphone'],
            'uphone_yz' => '1',
            'status' => '1',
            'upassword' =>$data['upassword'],
        ]);
        $this->allowField(true)->save($newdata);
        $this->userdb()->save($newdata);
        $arr = array(
            'uid' => $this->uid,
            'group_id' => $newdata['group_id'],
        );
        $this->usergroup()->save($arr);
        $newdata['uid'] = $this->uid;
        if(!empty($data['tuijian']) && isset($data['tuijian'])){
            $add['puid'] = $this->where('username',$data['tuijian'])->value('uid');
            $add['uid'] = $newdata['uid'];
            model('UserSpread')->save($add);
        }
        return $newdata;
    }
    //  找回密码
    public function repwd($data){
        $user = $this->where('username',$data['username'])->find();
        $data['upassword'] = pwd($data['upassword']);
        $user->isUpdate(true)->save(['upassword'  => $data['upassword']],['uid' => $user['uid']]);
        if($user['uid'] !== '1'){
            $user->userdb->save(['upassword'  => $data['upassword']],['uid' => $user['uid']]);
        }
        return true;
    }

}