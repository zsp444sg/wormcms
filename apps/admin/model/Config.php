<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\admin\model;
use think\Model;
class Config extends Model{
    // 配置类型转换
    protected function getConfTypezhAttr($value,$data){
        $conf_type = [ 1 => '单行文本框',2 => '多行文本框',3 => '单选按钮',4 => '复选框',5 => '下拉菜单',6 => '附件'];
        return $conf_type[$data['conf_type']];
    }
    // 附件类型转换
    protected function getConfTypelxzhAttr($value,$data){
        $conf_typelx = [ 1 => '图片',0 => '压缩包',2 => '文档',3 => ''];
        return $conf_typelx[$data['conf_typelx']];
    }
    public function open(){
        $map = '';
        if(session('_admin_.uope') == 1){
            $map['open'] = '1';
        }
        $open = $this->where($map)->order('sort desc')->paginate(20);
        return $open;
    }


}