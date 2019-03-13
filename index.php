<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用入口文件
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//本地开发配置文件

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

//定义插件目录
define('PLUGINS','./Plugins');

// 定义应用目录
define('APP_PATH','./Application/');

/**
 * 自定义网站HostURl
 */
if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
    $server_request_scheme = 'https';
} else {
    $server_request_scheme = 'http';
}
define ( '__HOST_URL__', $server_request_scheme.'://'.$_SERVER['HTTP_HOST']);
//define ( '__HOST_URL__', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);


// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

 