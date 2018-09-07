<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-21
 * Time: 17:32
 */
namespace app\common\controller;

class Indexbase extends Base {
    public $cxbsuser;
    public $temp;
    public $cxbsuserauth;
    public function _initialize(){
        parent::_initialize();
        //  检测是否启用手机端
        $mobileurl = false;
        if($this->ismobile()){
            if($this->webdb['wapopen'] == '0' || empty($this->webdb['wapopen'])){
                $mobileurl = true;
            }else{
                $strurl = request()->url();
                $strmodule = request()->module();
                $mobileurl = url('mobile/Index/index');
                if(($position=strpos($strurl,$strmodule))!==false){
                    $mobileurl = preg_replace("/{$strmodule}/",'mobile',$strurl,1);
                }
                $this->redirect($mobileurl);
            }
        }
        //  检测用户状态
        $this->user();
        //  加载模板目录
        $this->temps();
        //  获取网站导航
        $this->navs($mobileurl);
        $this->footnav();
        //  获取友情链接
        $this->linklist();
        //  获取编辑权限
        $this->cxbsuserauth = $this->cxbsuserauth();
        $this->assign([
            'cxbsuser' => $this->cxbsuser,
            'webdb' => webdb(),
            'mobileurl' => $mobileurl,
            'bodyondblclick' => $this->cxbsuserauth,
        ]);
    }
    //  加载默认模板文件
    public function temps($plate = null){
        if($plate == null){
            $plate = $this->webdb['temp'];
        }
        if(is_dir(config('template.view_path').$plate)){
            $this->temp = config('template.view_path').$plate.'/';
        }else{
            $this->temp = config('template.view_path').'default/';
        }
        if(is_file($this->temp.'head.htm')){
            $temps['head'] = $this->temp.'head.htm';
        }else{
            $temps['head'] = config('template.view_path').'default/head.htm';
        }
        if(is_file($this->temp.'foot.htm')){
            $temps['foot'] = $this->temp.'foot.htm';
        }else{
            $temps['foot'] = config('template.view_path').'default/foot.htm';
        }
        $this->assign('temps',$temps);
        return true;
    }
    //  获取网站导航
    public function navs($mobileurl){
        if($mobileurl){
            $map['seetype'] = ['in','0,2'];
        }else{
            $map['seetype'] = ['in','0,1'];
        }
        $map['status'] = '1';
        $navs = model('Nav')->where($map)->whereOr('seetype','NULL')->order('sort desc,id asc')->select();
        if(empty($navs)){
            return true;
        }
        $navdata = $this->navfat($navs);
//        halt($navdata);
        $this->assign('navs',$navdata);
        return true;
    }
    //  获取网站底部导航
    public function footnav(){
        $navs = model('Nav')->where('seetype','3')->order('sort desc,id asc')->select();
        if(empty($navs)){
            return true;
        }
        $navdata = $this->navfat($navs);
        $this->assign('footnav',$navdata);
        return true;
    }
    //  格式化导航
    public function navfat($data){
        $upmodel = new Upload();
        foreach ($data as $key => $val){
            if($val['status'] != '1'){
                unset($data[$key]);
                continue;
            }
            $icons = unserialize($val['icon']);
            if(!empty($icons['class']) && $icons['class'] == '1'){
                $icons['icon'] = $upmodel->editadd($icons['icon'],false);
            }
            $val['icon'] = $icons;
            $val['url'] = url($val['url']);
            $pids[] = $val['pid'];
        }
        $pids = arrayNull($pids);
        $pids = arrayFlip($pids);
        $navdata['navs'] = $data;
        $navdata['pids'] = implode(',',$pids);
        return $navdata;
    }
    //  获取编辑权限
    protected function cxbsuserauth(){
        $bodyondblclick = '';
        if(session('_admin_') && session('userdb') == session('_admin_')){
            $bodyondblclick = "class='cx-bodydbclick' data-type='bodyonclick'";
        }
        return $bodyondblclick;
    }
    //  获取友情链接
    public function linklist(){
        $linklist = model('Link')->seelink();
        //  获取友情链接分类
        $class = model('LinkClass')->linkclass();
        $tlink = $plink = '';
        if(!empty($linklist['tlink'])){
            foreach ($class as $v){
                $v['title'] = $v['title'];
                if(empty($linklist['tlink'][$v['id']])){
                    continue;
                }
                $v['list'] = $linklist['tlink'][$v['id']];
                $tlink[] = $v;
            }
        }
        if(!empty($linklist['imglink'])){
            $plink = $linklist['imglink'];
            $upmodel = new Upload();
            foreach ($plink as &$v){
                $v['picurl'] = $upmodel->editadd($v['picurl'],false);
            }
        }
        $this->assign([
            'tlink' => $tlink,
            'plink' => $plink
        ]);
        return true;
    }
    //  写入日志
    public function addlog($data){
        $logs = new LogsAdope();
        $log = ([
            'title' => $data,
            'username' => session('_admin_.uname')
        ]);
        $logs->save($log);
    }
    //  检测用户是否登录
    public function user(){
        $user = "";
        $reg = url('Login/reg');
        $login = url('Login/login');
        if(session('userdb')){
            $usericon = session('userdb.uicon');
            $user .= "<a class=\"cx-top-reg cx-click hidden-s hidden-l\" data-type=\"regsee\" data-href=\"{url('member/index/index')}\" data-title=\"新用户注册\"><i class=\"cx-icon cx-icon-unie64d mr-5\"></i>会员中心</a>";
        }else{
            $user .= "<a class=\"cx-click hidden-s hidden-l\" data-type=\"regsee\" data-href=\"{$login}\" data-title=\"用户登录\">登录</a><a class=\"cx-top-reg cx-click hidden-s hidden-l\" data-type=\"regsee\" data-href=\"{$reg}\" data-title=\"新用户注册\">免费注册</a>";
        }
        $this->assign([
            'user' => $user,
        ]);
        return true;
    }
    //  判断客户端
    function ismobile() {
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;
        if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
            return true;
        if (isset ($_SERVER['HTTP_VIA']))
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}