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
use app\common\model\Sms as cxModel;

class Sms extends Adminbase {

    public function index(){
        $cxmodel = new cxModel();
        $list = $cxmodel->order('addtime desc,id desc')->paginate(30);
        $this->assign('list',$list);
        return view();
    }
    public function edit(){
        $cxmodel = new cxModel();
        $data = request()->param();
        $edit = $cxmodel->where('id',$data['id'])->find();
        if($edit['status'] == '0'){
            $cxmodel->isUpdate(true)->save(['status'=> '1'],['id'=> $edit['id']]);
        }
        $this->assign('edit',$edit);
        return view();
    }
    //  删除
    public function del(){
        if(!request()->param('id')){
            $this->error('访问错误！');
        }
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $deldata = cxModel::get(request()->param('id'));
            $title = $deldata['title'];
            if($deldata->delete()){
                $this->addlog("删除通知【{$title}】成功！");
                $this->success("删除通知【{$title}】成功！",'index');
            }else{
                $this->error("删除通知【{$title}】失败！");
            }
        }
        return;
    }
    public function pdel(){
        if(request()->isPost()){
            $cxmodel = new cxModel();
            $data = input('post.');
            $data = datatrim($data);
            foreach ($data['pdel'] as $v){
                $deldata = $cxmodel->where('id',$v)->find();
                $title = $deldata['title'];
                $deldata->delete();
                $this->addlog("删除通知【{$title}】成功！");
            }
            $this->success("批量删除通知成功！");
        }
        return;
    }
}
