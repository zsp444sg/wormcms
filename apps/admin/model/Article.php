<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2017-09-07
 * Time: 11:47
 */
namespace app\admin\model;
use think\Db;
use think\Model;
class Article extends Model{
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['addtime','addip'];
    protected $update = ['edittime','editip'];
    protected $type = [
        'addtime'    => 'timestamp',
        'edittime'    => 'timestamp',
    ];
    // 定义时间戳字段名
    protected $createTime = 'addtime';
    protected $updateTime = 'edittime';
    protected function setAddipAttr(){
        return request()->ip();
    }
    protected function setEditipAttr(){
        return request()->ip();
    }
    // 启用状态
    protected function getStatusIconAttr($value,$data){
        $status = [ 0 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/see').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-iconset0187 t-gray"></i></a>',1 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/see').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-zhengchang t-green"></i></a>',2 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/see').'" class="button button-S cx-click bg-green" data-type="sestatus"><i class="cx-icon cx-icon-huanyuanmulu"></i> 还原</a>'];
        return $status[$data['status']];
    }
    // 启用状态
    protected function getJianIconAttr($value,$data){
        $jian = [ 0 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/jian').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-good t-gray"></i></a>',1 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/jian').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-good t-red"></i></a>'];
        return $jian[$data['jian']];
    }
    // 启用状态
    protected function getTopIconAttr($value,$data){
        $top = [ 0 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/top').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-stick_icon t-gray"></i></a>',1 => '<a data-id="'.$data['aid'].'" data-href="'.url('Article/top').'" class="cx-click" data-type="sestatus"><i class="cx-icon cx-icon-stick_icon t-red"></i></a>'];
        return $top[$data['top']];
    }
    //  处理基本数据
    public function addbase($data){
        if(!empty($data['keyword']) && isset($data['keyword'])){
            $data['keyword'] = str_replace(array(' ','，'),',',$data['keyword']);
        }
        if(!empty($data['sid']) && isset($data['sid'])){
            $data['sid'] = implode(',',$data['sid']);
        }
        if(!empty($data['fuid']) && isset($data['fuid'])){
            $data['fuid'] = implode(',',$data['fuid']);
        }
        if(empty($data['status']) && !isset($data['status'])){
            $data['status'] = 0;
        }
        if(empty($data['jian']) && !isset($data['jian'])){
            $data['jian'] = 0;
        }
        if(empty($data['top']) && !isset($data['top'])){
            $data['top'] = 0;
        }
        if(!empty($data['seegroup']) && isset($data['seegroup'])){
            $data['seegroup'] = implode(',',$data['seegroup']);
        }
        if(!empty($data['dontgroup']) && isset($data['dontgroup'])){
            $data['dontgroup'] = implode(',',$data['dontgroup']);
        }
        if(!empty($data['template']) && isset($data['template'])){
            $data['template'] = serialize($data['template']);
        }
        return $data;
    }
    //  获取附加表字段
    public function futabel($mid){
        $tabelname = config('database.prefix').'article_content_'.$mid;
        $fields = Db::query("DESC $tabelname");
        $arr = '';
        foreach ($fields as $v){
            $arr[] = $v['Field'];
        }
        return $arr;
    }


}