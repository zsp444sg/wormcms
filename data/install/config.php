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
return [

    // 默认模块名
    'default_module'         => 'install',
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板路径
        'view_path'    => APP_PATH.'install/template/',
        // 模板后缀
        'view_suffix'  => 'htm',
        // 模板文件名分隔符
        'view_depr'    => '_',
    ],
    //常用路径
    'view_replace_str'  =>  [
        '__IMAGES__'=> '/images/install',
        '__EDITOR__'=> '/public/wangEditor',
        '__LAYUI__'=> '/public/layui',
        '__CSS__'=> '/public/css',
        '__JS__'=> '/public/js',
        '__IMG__'=> '/public/img',
    ],

];
