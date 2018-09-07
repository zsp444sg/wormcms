<?php
// +----------------------------------------------------------------------
// | 火凤凰CMS内容管理系统
// +----------------------------------------------------------------------
// | Copyright (c) 2015~2018 http://cxbs.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 赵志广 <amdin@cxbs.net>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\controller\Adminbase;

class Chinacode extends Adminbase {

    public function cha(){
        if(request()->isPost()){
            $data = input('post.');
            $data = datatrim($data);
            $cxmodel = new \app\common\model\Chinacode();
            $codelist = $cxmodel->chalist($data);
            return $codelist;
        }
    }
}
