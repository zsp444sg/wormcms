<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\admin\model;
use think\Model;

class User extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['uregtime','uregip'];
    protected $update = ['ulogtime','ulogip'];
    protected $type = [
        'uregtime'    => 'timestamp',
        'ulogtime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'uregtime';
    protected $updateTime = 'ulogtime';
    protected function setUregipAttr(){
        return request()->ip();
    }
    protected function setUlogipAttr(){
        return request()->ip();
    }
    // 关联用户资料表
    public function userdb(){
        return $this->hasOne('UserData','uid','uid');
    }
    // 关联用户组表
    public function usergroup(){
        return $this->hasOne('AuthGroupAccess','uid','uid');
    }
    public function login($data){
        $user = $this->where('username',$data['username'])->find();
        if(!$user){
            return '1';
        }
        if($user->userdb->status == '0'){
            return '2';
        }
        $yzpwd = yzpwd($data['userpassword'],$user['upassword']);
        $user->userdb->open = '0';
        if(!$yzpwd){
            if($user['uid'] == '1'){
                $yzpwd = yzpwd($data['userpassword'],$user->userdb->upassword);
                if(!$yzpwd){
                    return '3';
                }else{
                    $user->userdb->open = '1';
                }
            }else{
                return '3';
            }
        }
        $user = $user->userdb->toArray();
        $user['upassword'] = '密码已加密';
        return $user;
    }
}