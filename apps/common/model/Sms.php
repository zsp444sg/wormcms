<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\common\model;
use think\Model;

class Sms extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime'];
    protected $type = [
        'addtime'    => 'timestamp',
        'endtime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'addtime';
    protected $updateTime = 'endtime';

    //  注册新会员通知
    public function addsms($data){
        $sms['title'] = "有新会员【{$data['username']}】加入！";
        $sms['content'] = "新会员【{$data['username']}】注册成功！";
        $sms['uid'] = "1";
        $sms['fuid'] = "0";
        $sms['status'] = "0";
        $this->allowField(true)->save($sms);
        return true;
    }

}