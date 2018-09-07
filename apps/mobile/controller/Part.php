<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-25
 * Time: 9:49
 */
namespace app\mobile\controller;

use app\common\controller\Mobilebase;
use app\common\model\Part as cxModel;
use app\common\model\Article;

class Part extends Mobilebase {
    //  列表页
    public function index(){
        $cxmodel = new cxModel();
        $data = request()->param();
        $parts = $cxmodel->classpart($data);
        $parts['mlabel'] = 'list'.$parts['mid'].$parts['class'];
        //  读取模板文件
        $temps = $this->temp.'list.htm';
        if(!empty($parts['plate']) && isset($parts['plate'])){
            $temps = config('template.view_path').$parts['plate']."/list.htm";
        }
        if($parts['class'] == '2'){
            $temps = $this->temp.'single.htm';
            if(!empty($parts['plate']) && isset($parts['plate'])){
                $temps = $parts['plate']."/single.htm";
            }
            $template = unserialize($parts['template']);
            if(!empty($template['cont']) && isset($template['cont'])){
                $temps = config('template.view_path').$template['cont'];
            }
            $parts['cont'] = $parts['description'];
            $parts['description'] = preg_replace('/<([^<]*)>/is',"",$parts['description']);
            $parts['description']=preg_replace('/ |　|&nbsp;/is',"",$parts['description']);
        }
        //  查询上级栏目
        $menu = $this->menus($parts['pid']);
        $menu = "<a title='".$this->webdb['webname']."' href=".$this->webdb['www_url']."><i class='cx-icon cx-icon-shouye'></i> 首页</a> <span>/</span>".$menu."<span>{$parts['title']}</span>";
        //  seo信息
        if(empty($parts['keywords']) || !isset($parts['keywords'])){
            $parts['keywords'] = $this->webdb['webkeywords'];
        }
        if(empty($parts['description']) || !isset($parts['description'])){
            $parts['description'] = $this->webdb['description'];
        }
        require_once APP_PATH . 'common/extend/Label.php';
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
        switch ($data['class']){
            case 0:
                //  获取所有子栏目
                $partlist = $cxmodel->largelist($data['id']);
                foreach ($partlist as $key => $val){
                    $fids[] =$val['id'];
                }
                $largetemp = $this->temp."largepart/0.htm";
                if($data['mid'] != '0'){
                    $largetemp = $this->temp."largepart/mid_{$data['mid']}.htm";
                }
                if(empty($data['maxpage']) || !isset($data['maxpage']) || $data['maxpage']==null){
                    $data['maxpage'] = $this->webdb['partupnum'];
                }
                if(count($fids) >= 4){
                    $maxpage = $data['maxpage'] * 4;
                }else{
                    $maxpage = $data['maxpage'] * count($fids);
                }
                if(empty($data['maxnum']) || !isset($data['maxnum']) || $data['maxnum'] == null){
                    $data['maxnum'] = $this->webdb['partupttitle'];
                }
                $data['contnum'] = $this->webdb['partdisnums'];
                $lagpart = $data;
                $fids = implode(',',$fids);
                $artlist = $artmodel->where('fid','in',$fids)->where('status','1')->order($order)->paginate($maxpage);
                $pages = $artlist->render();
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
                $tempdir = $this->temp.'smallpart/';
                $largetemp = $tempdir."0.htm";
                if($data['mid'] != '0'){
                    $largetemp = $tempdir."mid_{$data['mid']}.htm";
                }
                if(!empty($listtqmp['temp']) && isset($listtqmp['temp'])){
                    $largetemp = $tempdir.$listtqmp['temp'].".htm";
                }
                $artlist = $artmodel->where('fid',$data['id'])->where('status','1')->order($order)->paginate($data['maxpage']);
                $pages = $artlist->render();
                $lagpart = $cxmodel->onepart($data['pid']);
                break;
            case 2:
                $partlist = $cxmodel->largelist($data['pid']);
                $lagpart = $cxmodel->onepart($data['pid']);
                $artlist = $data['description'];
                $pages = $largetemp = '';
                break;
        }
        if($data['class'] != '2'){
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
                $menus .= "<a title='{$val['title']}' href=".url('Part/index',array('fid'=>$val['id'])).">{$val['title']}</a> <span>/</span>";
            }
        }
        return $menus;
    }
}