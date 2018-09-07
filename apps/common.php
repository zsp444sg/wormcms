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

// 格式化数组
function datatrim($data){
    $trim = array();
    foreach ($data as $key => $val){
        if(is_array($val)){
            $trim[$key] = datatrim($val);
        }else{
            $val = trim($val);
            $val = str_replace("\t","   &nbsp;  &nbsp;",$val);
            $val = str_replace("   "," &nbsp; ",$val);
            $trim[$key] = trim($val);
        }
    }
    return $trim;
}
/*
 * 获取网站配置项
 */
function webdb(){
    if(webCacheHas('webdb')){
        $webdb = webCacheGet('webdb');
    }else{
        $confModel = new \app\common\model\Config();
        $data = $confModel->select();
        $webdb = [];
        foreach ($data as $k => $v){
            $webdb[$v['conf']] = $v['conf_value'];
        }
        webCacheSet('webdb',$webdb);
    }
    return $webdb;
}
/*
 *  生成验证码
 */
function incode($fontsize = '30',$length = '6'){
    $config =    [
        'useCurve'    =>    false,
        'useNoise'    =>    false,
        'fontSize'    =>    $fontsize,
        'length'      =>    $length,
        'useNoise'    =>    true,
    ];
    $incode = new \think\captcha\Captcha($config);
    return $incode->entry();
}
/*
 *  密码加密
 */
function pwd($pwd){
    $salt = base64_encode(substr(md5($pwd),6,18)).base64_encode(substr(md5(rand()),6,18));
    $salt = sha1($salt.$pwd).$salt;
    return $salt;
}
function yzpwd($pwd,$pwdy){
    $salt = substr($pwdy,40,48);
    $salt = sha1($salt.$pwd).$salt;
    if($salt === $pwdy){
        return true;
    }else{
        return false;
    }
}
/*
 * 缓存加密
 */
function cxbsmd5($data){
    $j=0;
    $start = 0;
    $result = array();
    if (!is_string($data)) {
        return false;
    }
    $strlen = strlen($data);
    if (!$strlen) {
        return false;
    }
    while ($start < $strlen) {
        $result[$j] = substr($data, $start, 2 << $j);
        $start += (2 << $j);
        ++$j;
    }
    if ($strlen > 32) {
        $data = '';
    }
    while ($j > 0) {
        $data .= $result[--$j];
    }
    return md5($data);
}
/*
 * 添加web缓存
 */
function webCacheSet($name,$data,$cachetime = '3600'){
    $cxcache = new \app\common\controller\Cxcache();
    \think\Cache::connect($cxcache->webcache())->set($name,$data,$cachetime);
    return true;
}
/*
 * 查询 web缓存
 */
function webCacheHas($name){
    $cxcache = new \app\common\controller\Cxcache();
    return \think\Cache::connect($cxcache->webcache())->has($name);
}
/*
 * 获取web缓存
 */
function webCacheGet($name){
    $cxcache = new \app\common\controller\Cxcache();
    $data = \think\Cache::connect($cxcache->webcache())->get($name);
    if(!empty($data) && isset($data)){
        return $data;
    }else{
        return false;
    }
}
/*
 * 清除web缓存
 */
function webCacheRm($name){
    $cxcache = new \app\common\controller\Cxcache();
    \think\Cache::connect($cxcache->webcache())->rm($name);
    return true;
}
/*
 * 添加用户指定缓存
 */
function userCacheSet($uid,$name,$data){
    $cxcache = new \app\common\controller\Cxcache();
    \think\Cache::connect($cxcache->usercache($uid))->set($name,$data);
    return true;
}
/*
 * 查询用户指定缓存
 */
function userCacheHas($uid,$name){
    $cxcache = new \app\common\controller\Cxcache();
    return \think\Cache::connect($cxcache->usercache($uid))->has($name);
}
/*
 * 获取用户指定缓存
 */
function userCacheGet($uid,$name){
    $cxcache = new \app\common\controller\Cxcache();
    return \think\Cache::connect($cxcache->usercache($uid))->get($name);
}
/*
 * 清除用户指定缓存
 */
function userCacheRm($uid,$name){
    $cxcache = new \app\common\controller\Cxcache();
    \think\Cache::connect($cxcache->usercache($uid))->rm($name);
    return true;
}
/*去除空值*/
function arrayNull($data){
    foreach ($data as $key => $val){
        if(is_array($val)){
            $data[$key] = arrayNull($val);
        }else{
            if(empty($val) || !isset($val)){
                unset($data[$key]);
            }
        }
    }
    return $data;
}
/*去除重复*/
function arrayFlip($data){
    $data = array_flip($data);
    $data = array_flip($data);
    return $data;
}

/*写入用户操作*/
function userOperation($name,$edit){
    $cxmodel = new \app\common\model\UserOperation();
    $cxmodel->save(['username'=>$name,'title'=>$edit]);
    return true;
}
/*检查字段是否重复*/
function table_fields($table,$field=''){
    $tabelname = config('database.prefix').$table;
    $fields = \think\Db::query("DESC $tabelname");
    $list = array();
    foreach ($fields as $k => $v){
        $list[] = $v['Field'];
    }
    if(in_array($field,$list)){
        return false;
    }
    return true;
}
/**
 *截取字符
 **/
function get_word($string,$length,$more=1,$dot = '..') {
    $more || $dot='';
    if(strlen($string) <= $length) {
        return $string;
    }
    $pre = chr(1);
    $end = chr(1);
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);
    $strcut = '';
    if( 1 ) {
        $n = $tn = $noc = 0;
        while($n < strlen($string)) {

            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) {
                break;
            }
        }
        if($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        $_length = $length - 1;
        for($i = 0; $i < $length; $i++) {
            if(ord($string[$i]) <= 127) {
                $strcut .= $string[$i];
            } else if($i < $_length) {
                $strcut .= $string[$i].$string[++$i];
            }
        }
    }
    $strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    $pos = strrpos($strcut, chr(1));
    if($pos !== false) {
        $strcut = substr($strcut,0,$pos);
    }
    return $strcut.$dot;
}
/*  写入 session('_admin_',$user);*/
function addsession($name,$data){
    session($name,$data);
}

/*
 *  清空用户信息
 */
function loginout($uid){
    $cxcache = new \app\common\controller\Cxcache();
    \think\Cache::connect($cxcache->usercache($uid))->clear();
    session(null);
    return true;
}
/*
 *  导入数据库文件
 */
function parse_sql($sql = '', $prefix = [], $limit = 0) {
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';
    // 替换表前缀
    if (!empty($prefix)) {
        $to   = current($prefix);
        $from = current(array_flip($prefix));
    }
    if ($sql != '') {
        // 纯sql内容
        $pure_sql = [];
        // 多行注释标记
        $comment = false;
        // 按行分割，兼容多个平台
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = explode("\n", trim($sql));
        // 循环处理每一行
        foreach ($sql as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }
            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }
            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }
            // 多行注释开始
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }
            // 多行注释结束
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }
            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }
            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`'.$from, '`'.$to, $line);
            }
            if ($line == 'BEGIN;' || $line =='COMMIT;') {
                continue;
            }
            // sql语句
            array_push($pure_sql, $line);
        }
        // 只返回一条语句
        if ($limit == 1) {
            return implode($pure_sql, "");
        }
        // 以数组形式返回sql语句
        $pure_sql = implode($pure_sql, "\n");
        $pure_sql = explode(";\n", $pure_sql);
        return $pure_sql;
    } else {
        return $limit == 1 ? '' : [];
    }
}
