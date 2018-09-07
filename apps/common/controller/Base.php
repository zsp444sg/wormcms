<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-21
 * Time: 17:32
 */
namespace app\common\controller;

use think\Controller;

class Base extends Controller{
    public $webdb;
    public function _initialize(){
        parent::_initialize();
        $this->webdb = webdb();
    }
    //  取随机数
    public function rands($res = ''){
        $rands = substr(md5($res.time()),0,8);
        return $rands;
    }

}