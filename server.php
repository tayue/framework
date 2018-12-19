<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:19
 */

use Framework\Web\Application;
use Framework\SwServer\ServerManager;
header("Content-type:text/html;charset=utf-8");
ini_set("display_errors", "On");
date_default_timezone_set('UTC');
error_reporting(E_ALL);
define("BASE_DIR", __DIR__);

include_once './autoloader.php';
include_once './App/Config/defines.php';
$config = include_once './App/Config/config.php';
$serverConfig = include_once './App/Config/server.php';
$config = array_merge($config, $serverConfig);
include_once VENDOR_PATH . '/autoload.php';

if ($config['is_swoole_http_server']) { //用swoole服务器启动
    $sm = ServerManager::getInstance();
    $sm->createServer($config);
    $sm->start();
}


