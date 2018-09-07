<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-25
 * Time: 9:49
 */
namespace app\home\controller;

use app\common\controller\Indexbase;
use app\common\model\Article as cxModel;
use app\common\model\Chinacode;

class Article extends Indexbase {

    public function index(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $cxcont = $cxmodel->find($getdata['aid']);
        $cxmodel->save(['hist'=>$cxcont['hist']+1],['aid'=>$cxcont['aid']]);
        $parts = $cxmodel->basi($cxcont);
        $parts['mlabel'] = 'article'.$cxcont['mid'];
        //  加载独立模板
        $pageclass['class'] = 'fid';
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
            $pageclass['class'] = 'fuid';
            $pageclass['fuid'] = $getdata['fuid'];
        }
        //  获取独立栏目菜单
        if(!empty($getdata['fuid']) && isset($getdata['fuid'])){
            $this->fupartlist($getdata['fuid']);
        }else{
            $part = $this->partlist($cxcont['fid']);
        }
        //  获取栏目模板
        $part = unserialize($part['template']);
        //  加载默认模板
        $temps = $tempdir.'article.htm';
        if($parts['mid'] != '0'){
            $temps = $tempdir.'article_'.$parts['mid'].'.htm';
        }
        //  加载栏目模板
        if(isset($part['cont']) && !empty($part['cont'])){
            $temps = $tempdir.$part['cont'];
        }
        if(isset($parts['listtemp']) && !empty($parts['listtemp']) && $parts['listtemp']){
            $temps = $tempdir.$parts['listtemp'];
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
        //  俊先专用
        $chinacode = new Chinacode();
        $diquname = '';
        if(!empty($getdata['chc']) && isset($getdata['chc'])){
            $diquname = $chinacode->where('zoneid',$getdata['chc'])->find();
            $this->assign('diquname',$diquname);
        }
        //  获取上一篇和下一篇
        $artpage = $this->articlepage($cxcont,$pageclass,$diquname);
        $webs = array(
            'title' => $cxcont['title'].'-'.$this->webdb['webname'],
            'keywords' => $cxcont['keywords'],
            'description' => $cxcont['description'],
        );

//        halt($cxcont);
        $this->assign([
            'contdb' => $cxcont,
            'artpage' => $artpage,
            'webs' => $webs,
        ]);
        return view($temps);
    }
    //  生成栏目菜单
    public function partlist($fid){
        $partdata = model('Part')->where('id',$fid)->find();
        $partlist = model('Part')->allpart();
        $lagpart = model('Part')->onepart($partdata['pid']);
        //  查询上级栏目
        $menu = $this->menus($partdata['pid']);
        $menu = "<a title='".$this->webdb['webname']."' href=".$this->webdb['www_url']."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menu."<a title='".$this->webdb['webname']."' href=".url('Part/index',array('fid'=>$partdata['id'])).">{$partdata['title']}</a>";
        $this->assign([
            'partlist' => $partlist,
            'lagpart' => $lagpart,
            'menu' => $menu,
        ]);
        return $partdata;
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
    //  获取分页
    public function articlepage($data,$articlass,$diquname){
        $cxmodel = new cxModel();
        switch ($articlass['class']){
            case 'fid':
                $lastrs = $cxmodel->where('fid',$data['fid'])->where(array('aid' => array('lt',$data['aid']),'status' => 1))->order(array('aid'=>'DESC'))->find();
                $nextrs = $cxmodel->where('fid',$data['fid'])->where(array('aid' => array('gt',$data['aid']),'status' => 1))->order(array('aid'=>'ASC'))->find();
                if (!empty($lastrs) ) {
                    $ptitle = get_word($lastrs['title'],16,false);
                    $articlepage['prev'] = "<a title='{$lastrs['title']}' href='".url('Article/index',array('aid'=>$lastrs['aid']))."' >上一篇<span class='hidden-l'>：{$ptitle}</span></a>";
                    if(!empty($diquname) && isset($diquname)){
                        $articlepage['prev'] = "<a title='{$lastrs['title']}' href='".url('Article/index',array('aid'=>$lastrs['aid'],'chc'=>$diquname['zoneid']))."' >上一篇<span class='hidden-l'>：{$ptitle}</span></a>";
                    }
                } else {
                    $articlepage['prev'] = "<span class='t-gray'>没有了</span>";
                }
                if ( !empty($nextrs) ) {
                    $ntitle = get_word($nextrs['title'],16,false);
                    $articlepage['next'] = "<a title='{$nextrs['title']}' href='".url('Article/index',array('aid'=>$nextrs['aid']))."' >下一篇<span class='hidden-l'>：{$ntitle}</span></a>";
                    if(!empty($diquname) && isset($diquname)){
                        $articlepage['next'] = "<a title='{$nextrs['title']}' href='".url('Article/index',array('aid'=>$nextrs['aid'],'chc'=>$diquname['zoneid']))."' >下一篇<span class='hidden-l'>：{$ntitle}</span></a>";
                    }
                } else {
                    $articlepage['next'] = "<span class='t-gray'>没有了</span>";
                }
                break;
            case 'fuid':

                break;
        }
        return $articlepage;
    }
}