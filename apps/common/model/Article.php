<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-23
 * Time: 16:21
 */
namespace app\common\model;
use app\common\controller\Upload;
use think\Model;

class Article extends Model{
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $type = [
        'addtime'  =>  'timestamp',
    ];
    // 关联用户资料表
    public function articledb(){
        return $this->hasOne('ArtCont','aid','aid');
    }
    //  获取栏目基本信息
    public function basi($data){
        $data['template'] = unserialize($data['template']);
        if(!empty($data['template'])){
            foreach ($data['template'] as $k => $v){
                $data[$k.'temp'] = $v;
            }
        }
        unset($data['template']);
        return $data;
    }

    public function readcont($data,$partbase=''){
        $webdb = webdb();
        $rs = $data->toArray();
        $rs['mtitle'] && $rs['title'] = $rs['mtitle'];	//如果有简短标题的话,就使用简短标题
        $rs['title'] = $rs['futitle'] = preg_replace('/<([^<]*)>/is',"",$rs['title']);
        if(!empty($partbase['maxnum']) || isset($partbase['maxnum'])){
            $rs['title'] = get_word($rs['title'],$partbase['maxnum']);
        }
        $rs['url']= url('Article/index',array('aid'=>$rs['aid']));
        if(!empty($partbase['fuid']) && isset($partbase['fuid'])){
            $rs['fuurl']= url('Article/index',array('fuid'=>$partbase['fuid'],'aid'=>$rs['aid']));
        }
        //  读取模型信息
        if($rs['mid'] !== 0){
            $rs = $this->usmodelfields($rs);
        }
        $part = \model('Part')->onepart($rs['fid']);
        $rs['partname'] = $part['title'];
        //  图片
        if($rs['picurl']){
            $picurl = explode('/',$rs['picurl']);
            $rs['picurl3'] = '/'.$webdb['updir'].'/'.$rs['picurl'];		//1:1的图
            $rs['picurl'] = '/'.$webdb['updir'].'/'.$picurl['0']."/1x1_{$picurl['1']}";		//1:1的图
            $rs['picurl1'] = '/'.$webdb['updir'].'/'.$picurl['0']."/3x4_{$picurl['1']}";		//1:1的图
            $rs['picurl2'] = '/'.$webdb['updir'].'/'.$picurl['0']."/4x3_{$picurl['1']}";		//1:1的图
        }else{
            $rs['picurl1'] = $rs['picurl2'] = $rs['picurl3'] = "";
        }
        if(!isset($rs['description']) || empty($rs['description'])){
            $rs['description'] = model('ArtCont')->where('aid',$rs['aid'])->value('content');
        }
        $rs['description'] = str_replace(PHP_EOL, '', $rs['description']);
        $rs['description'] = preg_replace('/<([^<]*)>/is',"",$rs['description']);
        $rs['description'] = preg_replace('/ |　|&nbsp;/is',"",$rs['description']);	//把多余的空格去除掉
        if(!empty($partbase['contnum']) && isset($partbase['contnum'])){
            $rs['description'] = get_word($rs['description'],$partbase['contnum']);
        }
        $upmodel = new Upload();
        foreach ($rs as $key => $val){
            $rs[$key] = $upmodel->editadd($val,false);
        }
        $addtime = strtotime($rs['addtime']);
        $rs['time_Y']=date("Y",$addtime);
        $rs['time_W']=date("W",$addtime);
        $rs['time_y']=date("y",$addtime);
        $rs['time_m']=date("m",$addtime);
        $rs['time_d']=date("d",$addtime);
        $rs['time_H']=date("H",$addtime);
        $rs['time_i']=date("i",$addtime);
        $rs['time_s']=date("s",$addtime);
        return $rs;
    }
    //  对附加表进行读取解释
    public function usmodelfields($data){
        if(webCacheHas('artmodel'.$data['mid'])){
            $model = webCacheGet('artmodel'.$data['mid']);
        }else{
            $model = \model('ArtModel')->order('sort desc,id asc')->select();
            foreach ($model as $val){
                if($val['id'] == $data['mid']){
                    $model = $val;
                    webCacheSet('artmodle'.$data['mid'],$model);
                    break;
                }
            }
        }
        $upmodel = new Upload();
        $model = unserialize($model['config']);
        $fielddb = $model['field_db'];
        $edits = db("article_content_{$data['mid']}")->where('aid',$data['aid'])->find();
        foreach ($fielddb as $k => $v){
            if(!isset($edits[$k]) || empty($edits[$k]) || $edits[$k] == null){
                continue;
            }
            // 不处理textarea内容
//            if($v['formtype'] == 'textarea'){
//                $edits[$k] = preg_replace('/<br>/',"\r",$edits[$k]);
//            }else
            if($v['formtype'] == 'uptxt' || $v['formtype'] == 'upimg' || $v['formtype'] == 'upmv'){
                $edits[$k] = explode('&@&@&',$edits[$k]);
                $arr = '';
                foreach ($edits[$k] as $key => $val){
                    $arr[] = explode('@@@',$val);
                }
                $edits[$k] = null;
                foreach ($arr as $value){
                    $arrs['title'] = $value[0];
                    $arrs['value'] = $upmodel->editadd($value[1],false);
                    $edits[$k][] = $arrs;
                }
                unset($arr);
                unset($arrs);
            }elseif($v['formtype'] == 'upfile'){
                $edits[$k] = $upmodel->editadd($edits[$k],false);
            }elseif($v['formtype'] == 'diqu'){
                //  自订义的地区三联动
                $edits[$k] = explode(',',$edits[$k]);
            }
        }
        $edit = array_merge($edits,$data);
        return $edit;
    }

}