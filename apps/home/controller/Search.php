<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-04-25
 * Time: 9:49
 */
namespace app\home\controller;

use app\common\controller\Indexbase;
use app\common\model\Search as cxModel;

class Search extends Indexbase {
    public function index(){
        $cxmodel = new cxModel();
        $getdata = request()->param();
        $getdata = datatrim($getdata);
        $so['title'] = array('like',"%".$getdata['keyword']."%");
        $list =  model('Article')->where($so)->order('sort desc,aid asc')->paginate('20',false,['query' => request()->param()]);
        $pages = $list->render();
        $artmodel = new \app\common\model\Article();
        $solist = null;
        foreach ($list as $v){
            $solist[] = $artmodel->readcont($v);
        }
        $webs = array(
            'title' => $getdata['keyword'].'-'.$this->webdb['webname'],
            'keywords' => $getdata['keyword'],
            'description' => $getdata['keyword'],
        );
        $this->assign([
            'solist' => $solist,
            'webs' => $webs,
            'keyword' => $getdata['keyword'],
            'pages' => $pages,
        ]);
        return view($this->temp.'search.htm');
    }
}