<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-04
 * Time: 12:01
 */
namespace app\common\model;
use aliyun\SignatureHelper;
use think\Model;

class Smscode extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime','addip'];
    protected $type = [
        'addtime'    => 'timestamp',
    ];
// 定义时间戳字段名
    protected $createTime = 'addtime';
    protected function setAddipAttr(){
        return request()->ip();
    }

    /**
     * @param 手机号，用途，
     * @return 成功或失败
     */
    public function smscode($data){
        $webdb = webdb();
        $code = $this->generate_code();
        if(!empty($data['code']) && isset($data['code'])){
            $code = $data['code'];
        }
        webCacheSet('lishicode'.$data['phones'],$code,'300');
        // 取得AK信息
        $accessKeyId = $webdb['accesskeyidsms'];
        $accessKeySecret = $webdb['accesskeysecretsms'];
        // 短信接收号码
        $params["PhoneNumbers"] = $data['phones'];
        // 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $webdb['aliyunqianming'];
        // 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $data['tpcode'];
        // 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => $code,
        );
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );
        $new = get_object_vars($content);
        $add['phone'] = $data['phones'];
        $add['title'] = $data['title'];
        $add['code'] = $code;
        $add['status'] = '1';
        if($new['Code'] == 'OK'){
            $add['status'] = '1';
        }else{
            $add['status'] = '0';
        }
        $this->allowField(true)->isUpdate(false)->save($add);
        return $code;
    }
    //  生成验证码
    protected function generate_code($length = 4) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
}