<?php
/**
 * Created by PhpStorm.
 * User: 84071
 * Date: 2018-03-22
 * Time: 13:28
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/apps/');
define('CONF_PATH', __DIR__ . '/data/');
define('HACK_PATH', __DIR__.'/hack/'); //支付路径
//  检测安装文件是否存在
if(!is_file(APP_PATH.'app.lock')) {
    define('BIND_MODULE', 'install');
}
// 加载框架引导文件
require __DIR__ . '/cxcore/start.php';