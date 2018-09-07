<?php
namespace app\mobile\controller;

use app\common\controller\Mobilebase;

class Index extends Mobilebase {
    //  首页
    public function index(){
        $parts['mlabel'] = 'wap_index';
        $temps = $this->temp.'index.htm';
        require_once APP_PATH . 'common/extend/Label.php';
        $webs = array(
            'title' => $this->webdb['webname'],
            'keywords' => $this->webdb['webkeywords'],
            'description' => $this->webdb['description'],
        );
        $this->assign([
            'webs' => $webs,
        ]);
        return view($temps);
    }
}
