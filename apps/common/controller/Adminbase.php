<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-21
 * Time: 17:32
 */
namespace app\common\controller;

use app\common\model\LogsAdope;
use think\Cache;
use think\Request;

class Adminbase extends Base {
    public $cxbsuser;
    public function _initialize(){
        parent::_initialize();
        //  判断管理员是否登录
        if(!session('_admin_.uid') || !session('_admin_.username') || !session('_admin_.group_id')){
            return $this->redirect('Login/index');
        }
        //  获取用户信息及权限
        $cxcache = new Cxcache();
        if(Cache::connect($cxcache->usercache(session('_admin_.uid')))->get('cxbsuser')){
            $this->cxbsuser = Cache::connect($cxcache->usercache(session('_admin_.uid')))->get('cxbsuser');
        }else{
            $this->cxbsuser = session('_admin_');
            Cache::connect($cxcache->usercache(session('_admin_.uid')))->set('cxbsuser',session('_admin_'),3600);
        }
        if(!$this->auth($this->cxbsuser)){
            $this->error("你没有权限!");
        }
        $this->assign([
            'cxbsuser' => $this->cxbsuser,
            'webdb' => webdb(),
        ]);
    }
    /*
     * 权限验证
     */
    protected function auth($data){
        $auth = new Auth();
        $request = Request::instance();
        $name = $request->module().DS.$request->controller().DS.$request->action();
        $nocheck = array('User/edit/id=$data[\'uid\']',$request->module().DS.$request->controller().'/index','Login/loginqu');
        if($data['uid'] != 1 && !in_array($name,$nocheck) && !$auth->check($name,$data['uid'])){
            return false;
        }
        return true;
    }
    /*
     *  验证开关
     */
    public function btnauth($data){
        $data = explode(',',$data);
        $request = Request::instance();
        $auth = $this->adminmenu($this->cxbsuser['uid']);
        foreach ($auth as $k => $v){
            if($v['menusee'] == 1){
                $auths[] = strtolower($v['name']);
            }
        }
        foreach ($data as $k => $v){
            $name = strtolower($request->module().DS.$request->controller().DS.$v);
            $name = str_replace('\\','/',$name);
            if(in_array($name,$auths)){
                $btn[$v] = true;
            }else{
                $btn[$v] = false;
            }
        }
        return $btn;
    }
    /*
     * 获取菜单
     */
    public function adminmenu($uid){
        $auth = new Auth();
        if(userCacheHas($uid,'userauth')){
            $menu = userCacheGet($uid,'userauth');
        }else{
            $menu = $auth->userauth($uid);
        }
        return $menu;
    }
    /*获取模板文件*/
    public function tempnum($temp = null){
        $dir = 'data/template/';
        $temps = glob($dir.'*.php');
        if(!$temp){
            $temp = $this->webdb['temp'];
        }
        $option = "<option value='0'>默认风格</option>";
        foreach ($temps as $k => $v){
            unset($catalog);
            require $v;
            $catalog = datatrim($catalog);
            if($temp == $catalog['dir']){
                $option .= "<option selected value='{$catalog['dir']}'>{$catalog['title']}</option>";
            }else{
                $option .= "<option value='{$catalog['dir']}'>{$catalog['title']}</option>";
            }
        }
        return $option;
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

}