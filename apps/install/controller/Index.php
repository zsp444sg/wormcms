<?php
namespace app\install\controller;

use app\common\extend\Cxforms;
use think\Controller;
use think\Db;
use think\Loader;

class Index extends Controller {
    //  首页
    public function index(){
        session('error',false);
        session('step','index');
        return view();
    }
    public function step1(){
        $error = session('error');
        $step = session('step');
        if($error){
            $this->error('访问错误！','index');
        }
        if($step != 'index' && $step != '2'){
            $this->error('请同意使用协议！','index');
        }
        session('error',false);
        session('step','1');
        $xitong = $this->xitong();
        $hanshu = $this->hanshu();
        $check_dirs = $this->check_dirs();
        $this->assign([
            'xitong' => $xitong,
            'hanshu' => $hanshu,
            'check_dirs' => $check_dirs,
        ]);
        return view();
    }
    public function step2(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $sqldata = $data['db'];
            $webdata = $data['web'];
            $validate = Loader::validate('Install');
            if(!$validate->scene('sqldata')->check($sqldata)){
                $this->error($validate->getError());
            }
            if(!$validate->scene('webdata')->check($webdata)){
                $this->error($validate->getError());
            }
            //  检测数据库是否存在
            if($this->mysqljc($sqldata)){

                session('sqldata',serialize($sqldata));
                session('webdata',serialize($webdata));
                return $this->redirect('step3');
            }
            return;
        }
        $error = session('error');
        $step = session('step');
        if($error){
            $this->error('环境验证未通过！','index');
        }
        if($step != '1' && $step != '2'){
            $this->error('访问错误！','index');
        }
        session('error',false);
        session('step','2');
        $imgmd5 = cxbsmd5(request()->domain().substr(md5(time()),0,8));
        $saveName = '';
        for ( $i = 0; $i < 10; $i++ ) {
            $saveName .= substr($imgmd5, mt_rand(0, strlen($imgmd5) - 1), 1);
        }
        $this->assign('shibiefu',$saveName);
        return view();
    }
    public function step3(){
        $error = session('error');
        $step = session('step');
        if($error){
            $this->error('数据库和网站配置未通过！','step2');
        }
        if($step != '2'){
            $this->error('访问错误！','index');
        }
        echo $this->fetch();
        // 读取缓存
        $sqldata = unserialize(session('sqldata'));
        $webdata = unserialize(session('webdata'));
        // 导入SQL
        $formrb = new Cxforms();
        $sql_file = APP_PATH.'install/base.sql';
        if ($formrb->file_read($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, ['cx_' => $sqldata['prefix']] ,0);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                $this->showmsg('开始安装数据库...','t-green');
                foreach ($sql_list as $v) {
                    $vs = $v;
                    $tabelname = preg_replace("/^CREATE TABLE `(\w+)` .*/s","\\1",$vs);
                    if(!empty($tabelname)){
                        $this->showmsg("正在创建 {$tabelname}...","t-green");
                    }
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->showmsg("数据库安装失败，请重试","t-red");
                        return;
                    }
                }
            }
        }
        $this->showmsg('数据库安装完成，正在写入管理员帐号...','t-green');
        \db('config')->where('conf','webname')->setField('conf_value',$webdata['webname']);
        $user = array(
            'uid' => '1',
            'username' => $webdata['username'],
            'upassword' => pwd($webdata['userpassword']),
        );
        \db('user')->insert($user);
        $user['group_id'] = '1';
        $user['uregip'] = request()->ip();
        $user['uregtime'] = time();
        $user['ulogtime'] = time();
        $user['upassword'] = pwd($webdata['shibiefu']);
        $user['status'] = '1';
        \db('user_data')->insert($user);
        \db('auth_group_access')->insert($user);
        $this->showmsg('管理员帐号设置成功...','t-green');
        session('step','3');
        $this->success("数据库安装完成",'step4');
    }
    public function step4(){
        $error = session('error');
        $step = session('step');
        if($error){
            $this->error('数据库和管理员信息未写入！','step2');
        }
        if($step != '3'){
            $this->error('访问错误！','index');
        }
        session('step','4');
        $code = time();
        $formsrb = new Cxforms();
        $formsrb->file_write(APP_PATH.'app.lock', $code);
        return view();
    }
    public function showmsg($msg,$cl){
        echo "<script type='text/javascript'>showmsg('{$msg}','{$cl}');</script>";
        flush();
        ob_flush();
    }
    //  检测系统信息
    protected function xitong(){
        $xitong = array(
            'os' => array('操作系统','无限制','linux',PHP_OS,'success'),
            'php' => array('PHP版本','5.5','5.6',PHP_VERSION,'success'),
            'gd' => array('GD库','2.0','2.0','检测中...','success'),
            'upload' => array('附件上传','无限制','2MB','检测中...','success'),
            'dirs' => array('磁盘空间','100MB','>100MB','检测中...','success'),
        );
        //  检测php版本号
        if ($xitong['php'][3] < $xitong['php'][1]) {
            $xitong['php'][4] = 'error';
            session('error', true);
        }
        //  检测附件上传
        if(@ini_get('file_uploads')){
            $xitong['upload'][3] = ini_get('upload_max_filesize');
        }
        //  检测磁盘空间
        if(function_exists('disk_free_space')){
            $xitong['dirs'][3] = floor(disk_free_space(ROOT_PATH)/(1024*2)).' MB';
            if($xitong['dirs'][3] < 100){
                session('error', true);
            }
        }
        //  检测GD库
        $temparr = function_exists('gd_info') ? gd_info() : array();
        if(empty($temparr['GD Version'])){
            $xitong['gd'][3] = '未安装GD库';
            $xitong['gd'][4] = 'error';
            session('error',true);
        }else{
            $xitong['gd'][3] = $temparr['GD Version'];
        }

        unset($temparr);
        return $xitong;
    }
    //  检测函数
    protected function hanshu(){
        $hanshu = array(
            'pdo' => array('pdo()','支持','支持','success','class'),
            'pdo_mysql' => array('pdo_mysql()','支持','支持','success','mod'),
            'openssl' => array('openssl()','支持','支持','success','mod'),
            'gd' => array('gd()','支持','支持','success','mod'),
            'mbstring' => array('mbstring()','支持','支持','success','mod'),
            'zip' => array('zip()','支持','支持','success','mod'),
            'fileinfo' => array('fileinfo()','支持','支持','success','mod'),
            'curl' => array('curl()','支持','支持','success','mod'),
            'xml' => array('xml()','支持','支持','success','fons'),
            'mb_strlen' => array('mb_strlen()','支持','支持','success','fons'),
        );

        foreach ($hanshu as $k => $v) {
            if(('class'==$v[4] && !class_exists($k)) || ('mod'==$v[4] && !extension_loaded($k)) || ('fons'==$v[4] && !function_exists($k)) ) {
                $v[2] = '不支持';
                $v[3] = 'no';
                session('error', true);
            }
            $hanshu[$k] = $v;
        }
        return $hanshu;
    }
    //  检测写入权限
    protected function check_dirs(){
        $check_dirs = array(
            'runtime' => array('runtime','可写','可写','success','dir'),
            'upload_files' => array('upload_files','可写','可写','success','dir'),
            'conf' => array('data/config.php','可写','可写','success','file'),
            'dabase' => array('data/database.php','可写','可写','success','file'),
        );
        foreach ($check_dirs as $k => $v){
            if($v['4'] == 'dir'){
                if(!is_writable(ROOT_PATH.$v['0'])){
                    if(is_dir(ROOT_PATH.$v['0'])){
                        $v['2'] = '不可写';
                        $v['3'] = 'error';
                        session('error',true);
                    }else{
                        $v['2'] = '不存在';
                        $v['3'] = 'error';
                        session('error',true);
                    }
                }
            }else{
                if(file_exists(ROOT_PATH.$v['0'])){
                    if(!is_writable(ROOT_PATH.$v['0'])){
                        $v['2'] = '不存在';
                        $v['3'] = 'error';
                        session('error',true);
                    }
                }else{
                    $v['2'] = '不存在';
                    $v['3'] = 'error';
                    session('error',true);
                }
            }
            $check_dirs[$k] = $v;
        }
        return $check_dirs;
    }
    //  检测数据库信息
    protected function mysqljc($data){
        $config = include ROOT_PATH.'data/database.php';
        $data['type'] = 'mysql';
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $config) === false) {
                return $this->error('参数'.$k.'不存在！');
            }
        }
        $database = $data['database'];
        unset($data['database']);
        // 创建数据库连接
        $db_connect = Db::connect($data);
        try{
            $db_connect->execute('select version()');
        }catch(\Exception $e){
            $this->error('数据库连接失败，请检查数据库配置！');
        }
        // 创建数据库
        if (!$db_connect->execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8")) {
            return $this->error($db_connect->getError());
        }
        $data['database'] = $database;
        // 生成配置文件
        self::mkDatabase($data);
        return true;
    }
    //  生成配置文件
    protected function mkDatabase($data){
        $code = <<<INFO
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '{$data['hostname']}',
    // 数据库名
    'database'        => '{$data['database']}',
    // 用户名
    'username'        => '{$data['username']}',
    // 密码
    'password'        => '{$data['password']}',
    // 端口
    'hostport'        => '{$data['hostport']}',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '{$data['prefix']}',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => false,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
];
INFO;
        $formsrb = new Cxforms();
        $formsrb->file_write(ROOT_PATH.'data/database.php', $code);
        // 判断写入是否成功
        $config = include ROOT_PATH.'data/database.php';
        if ($config['password'] != $data['password'] && $config['username'] != $data['username']) {
            return $this->error('[data/database.php]数据库配置写入失败！');
            exit;
        }
    }
}
