<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-25
 * Time: 9:49
 */
namespace app\mobile\controller;

use app\common\controller\Mobilebase;
use app\common\model\FuPart as cxModel;
use app\common\model\Article;
use app\common\model\FuPartnav;

class FuPart extends Mobilebase {
    //  列表页
    public function index(){
        $cxmodel = new cxModel();
        $data = request()->param();
        $parts = $cxmodel->classpart($data);
        //  获取顶级栏目信息
        $partsleve = $this->fulevel($parts);
        $parts['mlabel'] = 'fulist_'.$parts['class'];
        if(!empty($partsleve['plate']) && isset($partsleve['plate']) && $parts['id'] == $partsleve['id']) {
            $parts['mlabel'] = 'fulist_'.$parts['class'].$partsleve['id'];
        }
        //  读取模板文件
        $temps = $this->temp.'fulist.htm';
        if(!empty($partsleve['plate']) && isset($partsleve['plate'])){
            $this->webdb['temp'] = $partsleve['plate'];
            $temps = config('template.view_path').$partsleve['plate']."/fulist.htm";
        }
        if(!empty($parts['plate']) && isset($parts['plate'])){
            $this->webdb['temp'] = $parts['plate'];
            $temps = config('template.view_path').$parts['plate']."/fulist.htm";
        }
        $template = unserialize($parts['template']);
        if(!empty($template['list']) && isset($template['list'])){
            $temps = config('template.view_path').$template['list'];
        }
        //  查询上级栏目
        $menus = $this->menus($parts['pid']);
        $menu = "<a title='".$this->webdb['webname']."' href=".$this->webdb['www_url']."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menus."<span>{$parts['title']}</span>";
        //  seo信息
        if(empty($parts['keywords']) || !isset($parts['keywords'])){
            $parts['keywords'] = $this->webdb['webkeywords'];
        }
        if(empty($parts['description']) || !isset($parts['description'])){
            $parts['description'] = $this->webdb['description'];
        }
        //  引入标签
        require_once APP_PATH . 'common/extend/Label.php';
        //  定义模板优先
        if(!empty($partsleve['plate']) && isset($partsleve['plate'])) {
            $this->temps($partsleve['plate']);
        }
        if(!empty($parts['plate']) && isset($parts['plate'])) {
            $this->temps($parts['plate']);
        }
        //  获取独立导航
        if($partsleve['funav'] == '1'){
            $this->funavs($partsleve['id'],false);
            $this->fuhome($partsleve,false);
            $menu = "<a title='".$partsleve['title']."' href=".url('FuPart/index?fuid='.$partsleve['id'])."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menus."<span>{$parts['title']}</span>";
        }
        //  内容列表
        $this->largelist($parts);
        $webs = array(
            'title' => $parts['title'].'-'.$this->webdb['webname'],
            'keywords' => $parts['keywords'],
            'description' => $parts['description'],
        );
        $this->assign([
            'webs' => $webs,
            'parts' => $parts,
            'menu' => $menu,
        ]);
        return view($temps);
    }
    //  查询顶级栏目信息
    public function fulevel($data){
        $cxmodel = new cxModel();
        if($data['pid'] == '0'){
            return $data;
        }
        $fulevel = $cxmodel->levelpart($data['pid']);
        return $fulevel;
    }
    //  获取独立导航
    public function funavs($id,$cha = true){
        $navmodel = new FuPartnav();
        $funavs = $navmodel->where('fid',$id)->where('status','1')->order('sort desc,id asc')->select();

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
    //  大分类处理
    public function largelist($data){
        $artlist = '';
        $cxmodel = new cxModel();
        $artmodel = new Article();
        switch ($data['listorder']){
            case 0:
                $order = 'top desc,jian desc,aid desc';
                break;
            case 1:
                $order = 'addtime desc';
                break;
            case 2:
                $order = 'addtime asc';
                break;
            case 3:
                $order = 'hist desc';
                break;
            case 4:
                $order = 'seetime desc';
                break;
            case 5:
                $order = 'top desc,jian desc,aid desc';
                break;
            case 6:
                $order = 'top desc,jian desc,aid desc';
                break;
        }
        if(!empty($data['listtqmp']) && isset($data['listtqmp'])){
            $listtqmp = unserialize($data['listtqmp']);
        }
        switch ($data['class']){
            case 0:
                //  获取所有子栏目
                $partlist = $cxmodel->largelist($data['id']);
                $fids = $pages = $aids = null;
                foreach ($partlist as $key => $v){
                    if($v['class'] == '0'){
                        continue;
                    }
                    $fids[] = $v['id'];
                }
                $tempdir = $this->temp.'fulargepart/';
                if(!is_dir($tempdir)){
                    $tempdir = config('template.view_path').'default/fulargepart/';
                }
                $largetemp = $tempdir."0.htm";
                if(!empty($listtqmp['temp']) && isset($listtqmp['temp'])){
                    $largetemp = $tempdir.$listtqmp['temp'].".htm";
                }
                if(empty($data['maxpage']) || !isset($data['maxpage']) || $data['maxpage']==null){
                    $data['maxpage'] = $this->webdb['partupnum'];
                }
                if($fids != null){
                    if(count($fids) >= 4){
                        $maxpage = $data['maxpage'] * 4;
                    }else{
                        $maxpage = $data['maxpage'] * count($fids);
                    }

                    if(empty($data['maxnum']) || !isset($data['maxnum']) || $data['maxnum'] == null){
                        $data['maxnum'] = $this->webdb['partupttitle'];
                    }
                    $data['contnum'] = $this->webdb['partdisnums'];
                    $fids = implode(',',$fids);
                    $fuids = model('FuArticle')->distinct(true)->field('aid')->where('fuid','in',$fids)->paginate($maxpage);
                    $pages = $fuids->render();
                    foreach ($fuids as $k => $v){
                        $aids[] = $v['aid'];
                    }
                    if(!empty($aids) && isset($aids) && $aids != null){
                        $aids = implode(',',$aids);
                        $artlist = $artmodel->where('aid','in',$aids)->where('status','1')->order($order)->select();
                    }
                }
                $lagpart = $data;
                break;
            case 1:
                if(empty($data['maxpage']) || !isset($data['maxpage']) || $data['maxpage']==null){
                    $data['maxpage'] = $this->webdb['partnum'];
                }
                if(empty($data['maxnum']) || !isset($data['maxnum']) || $data['maxnum'] == null){
                    $data['maxnum'] = $this->webdb['parttitle'];
                }
                $data['contnum'] = $this->webdb['partdisnums'];
                if(!empty($data['listtqmp']) && isset($data['listtqmp']) && $data['listtqmp'] != null){
                    $listtqmp = unserialize($data['listtqmp']);
                    if(!empty($listtqmp['listtqmp']) && isset($listtqmp['num']) && $listtqmp['num'] != null){
                        $data['contnum'] = $listtqmp['num'];
                    }
                }
                $partlist = $cxmodel->largelist($data['pid']);
                $tempdir = $this->temp.'fusmallpart/';
                if(!is_dir($tempdir)){
                    $tempdir = config('template.view_path').'default/';
                }
                $largetemp = $tempdir."fusmallpart/0.htm";
                if(!empty($listtqmp['temp']) && isset($listtqmp['temp'])){
                    $largetemp = $tempdir."fusmallpart/".$listtqmp['temp'].".htm";
                }
                $artlist = $artmodel->where('','EXP',"FIND_IN_SET({$data['id']},fuid)")->where('status','1')->order($order)->paginate($data['maxpage']);
                $pages = $artlist->render();
                $lagpart = $cxmodel->onepart($data['pid']);
                break;
        }
        if($data['class'] != '2' && !empty($artlist) && isset($artlist)){
            foreach ($artlist as $key => $val){
                $listdbs[] = $artmodel->readcont($val,$data);
            }
            if(!empty($listdbs) && isset($listdbs)){
                $artlist = $listdbs;
                $listdbs = null;
                unset($listdbs);
            }
        }
        $this->assign([
            'lagpart' => $lagpart,
            'partlist' => $partlist,
            'pages' => $pages,
            'artlist' => $artlist,
            'largetemp' => $largetemp,
        ]);
        return true;
    }
    //  生成面包屑
    public function menus($pid = '0'){
        $cxmodel = new cxModel();
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
    //  查询网站首页
    public function fuhome($data,$cha = true){
        $webdb = $this->webdb;
        if(!empty($data['logo']) && isset($data['logo']) && is_file(ROOT_PATH.$this->webdb['updir'].'/'.$data['logo'])){
            $webdb['weblogo'] = $data['logo'];
        }
        $webdb['www_url'] = url('FuPart/index',array('fuid'=>$data['id']));
        $this->assign([
            'webdb' => $webdb,
        ]);
        return true;
    }
}