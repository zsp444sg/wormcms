<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-25
 * Time: 9:49
 */
namespace app\mobile\controller;

use app\common\controller\Mobilebase;
use app\common\model\Article as cxModel;

class Article extends Mobilebase {

    public function index(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $cxcont = $cxmodel->find($getdata['aid']);
        $cxmodel->save(['hist'=>$cxcont['hist']+1],['aid'=>$cxcont['aid']]);
        $parts = $cxmodel->basi($cxcont);
        $parts['mlabel'] = 'article'.$cxcont['mid'];
        //  加载独立模板
        $tempdir = $this->temp;
        if(!empty($getdata['fuid']) && isset($getdata['fuid'])){
            $fucontroller = new \app\common\model\FuPart();
            $partsleve = $fucontroller->onepart($getdata['fuid']);
            if($partsleve['pid'] != '0'){
                $partsleve = $fucontroller->levelpart($partsleve['pid']);
            }
            if(!empty($partsleve['plate']) && isset($partsleve['plate'])){
                $this->webdb['temp'] = $partsleve['plate'];
                $tempdir = config('template.view_path').$partsleve['plate']."/";
            }
            if($partsleve['funav'] == '1'){
                $this->funavs($partsleve['id']);
            }
        }

        //  加载模板
        $temps = $tempdir.'article.htm';
        if($parts['mid'] != '0'){
            $temps = $tempdir.'article_'.$parts['mid'].'.htm';
        }
        if(isset($parts['listtemp']) && !empty($parts['listtemp']) && $parts['listtemp']){
            $temps = ROOT_PATH.$parts['listtemp'];
        }
        require_once APP_PATH . 'common/extend/Label.php';
        //  定义模板优先
        if(!empty($partsleve['plate']) && isset($partsleve['plate'])) {
            $this->temps($partsleve['plate']);
        }
        //  获取logo
        if(!empty($partsleve['logo']) && isset($partsleve['logo'])){
            $webdb = $this->webdb;
            $webdb['weblogo'] = $partsleve['logo'];
            $webdb['www_url'] = url('Fupart/index',array('fuid'=>$partsleve['id']));
            $this->assign([
                'webdb' => $webdb,
            ]);
        }
        $cxcont->articledb;
        $cxcont['articleconts'] = $cxcont['articledb']['content'];
        unset($cxcont['articledb']);
        unset($parts);
        $cxcont = $cxmodel->readcont($cxcont);
        //  获取独立栏目菜单
        if(!empty($getdata['fuid']) && isset($getdata['fuid'])){
            $this->fupartlist($getdata['fuid']);
        }else{
            $this->partlist($cxcont['fid']);
        }
        $webs = array(
            'title' => $cxcont['title'].'-'.$this->webdb['webname'],
            'keywords' => $cxcont['keywords'],
            'description' => $cxcont['description'],
        );
        $this->assign([
            'contdb' => $cxcont,
            'webs' => $webs,
        ]);
        return view($temps);
    }
    //  生成栏目菜单
    public function partlist($fid){
        $partdata = model('Part')->where('id',$fid)->find();
        $partlist = model('Part')->largelist($partdata['pid']);
        $lagpart = model('Part')->onepart($partdata['pid']);
        //  查询上级栏目
        $menu = $this->menus($partdata['pid']);
        $menu = "<a title='".$this->webdb['webname']."' href=".$this->webdb['www_url']."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menu."<a title='".$this->webdb['webname']."' href=".url('Part/index',array('fid'=>$partdata['id'])).">{$partdata['title']}</a>";
        $this->assign([
            'partlist' => $partlist,
            'lagpart' => $lagpart,
            'menu' => $menu,
        ]);
        return true;
    }
    //  辅助栏目菜单
    public function fupartlist($fuid){
        //  获取当前栏目信息
        $fumodel = new \app\common\model\FuPart();
        $lagpart = $fumodel->onepart($fuid);
        if($lagpart['pid'] != '0'){
            $lagpart = $fumodel->onepart($lagpart['pid']);
        }
        //  取得所有下级栏目
        $partlist = $fumodel->largelist($lagpart['id']);
        //  获取面包屑
        $menu = $this->fumenus($lagpart['pid']);
        $menu = "<a title='".$this->webdb['webname']."' href=".$this->webdb['www_url']."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menu."<a title='".$this->webdb['webname']."' href=".url('fuPart/index',array('fuid'=>$lagpart['id'])).">{$lagpart['title']}</a>";
        $this->assign([
            'partlist' => $partlist,
            'lagpart' => $lagpart,
            'menu' => $menu,
            'fuart' => 'fuart',
        ]);
        return true;
    }
    //  生成面包屑
    public function menus($pid = '0'){
        $cxmodel = new \app\common\model\Part();
        $menus = '';
        if($pid == '0'){
            return $menus;
        }
        $upmenus = $cxmodel->menus($pid);
        //  生成面包屑
        if(!empty($upmenus) || isset($upmenus)){
            foreach ($upmenus as $val){
                $menus .= "<a title='{$val['title']}' href=".url('Part/index',array('fid'=>$val['id'])).">{$val['title']}</a> <span>/</span>";
            }
        }
        return $menus;
    }
    //  独立面包屑
    public function fumenus($pid = '0'){
        $cxmodel = new \app\common\model\FuPart();
        $menus = '';
        if($pid == '0'){
            return $menus;
        }
        $upmenus = $cxmodel->menus($pid);
        //  生成面包屑
        if(!empty($upmenus) || isset($upmenus)){
            foreach ($upmenus as $val){
                $menus .= "<a title='{$val['title']}' href=".url('FuPart/index',array('fuid'=>$val['id'])).">{$val['title']}</a> <span>/</span>";
            }
        }
        return $menus;
    }
    //  获取独立导航
    public function funavs($id,$cha = true){
        $funavs = model('FuPartnav')->where('fid',$id)->where('status','1')->order('sort desc,id asc')->select();
        if(empty($funavs) || !isset($funavs)){
            return true;
        }
        foreach ($funavs as $key => $val){
            $val['url'] = url($val['url']);
            $pids[] = $val['pid'];
        }
        $pids = '';
        if(!empty($pids) && isset($pids)){
            $pids = arrayNull($pids);
            $pids = arrayFlip($pids);
            $pids = implode(',',$pids);
        }
        $navdata['navs'] = $funavs;
        $navdata['pids'] = $pids;
        $this->assign('navs',$navdata);
        return true;
    }
}