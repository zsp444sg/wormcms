<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-21
 * Time: 17:32
 */
namespace app\common\controller;

class Mobilebase extends Base {
    public $cxbsuser;
    public $temp;
    public $cxbsuserauth;
    public function _initialize(){
        parent::_initialize();
        //  加载模板目录
        $this->temps();
        //  获取网站导航
        $navs = $this->navs();
        //  获取编辑权限
        $this->cxbsuserauth = $this->cxbsuserauth();
        $this->assign([
            'cxbsuser' => $this->cxbsuser,
            'webdb' => webdb(),
            'navs' => $navs,
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
    public function navs(){
        $navs = model('Nav')->where('status','1')->order('sort desc,id asc')->select();
        foreach ($navs as $key => $val){
            if($val['status'] == '0'){
                unset($navs[$key]);
            }
            $val['url'] = url($val['url']);
            $pids[] = $val['pid'];
        }
        $pids = arrayNull($pids);
        $pids = arrayFlip($pids);
        $navdata['navs'] = $navs;
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