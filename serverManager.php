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

function initCheck()
{
    if (version_compare(phpversion(), '7.0.0', '<')) {
        die("php version must >= 7.0.0");
    }
    if (version_compare(swoole_version(), '1.9.15', '<')) {
        die("swoole version must >= 1.9.15");
    }
}

function opCacheClear()
{
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
    }
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

function commandParser()
{
    global $argv;
    $command = isset($argv[1]) ? $argv[1] : null;
    $server = isset($argv[2]) ? $argv[2] : null;
    return ['command' => $command, 'server' => $server];
}

//if ($config['is_swoole_http_server']) { //用swoole服务器启动
//    $sm = ServerManager::getInstance();
//    $sm->createServer($config);
//    $sm->start();
//}

function help($command)
{
    switch (strtolower($command . '-' . 'help')) {
        case 'start-help':
            {
                echo "------------serverManager启动服务命令------------\n";
                echo "1、执行php serverManager start http 即可启动http server服务\n\n";
                echo "\n";
                break;
            }
        case 'stop-help':
            {
                echo "------------serverManager终止服务命令------------\n";
                echo "1、执行php serverManager stop http 即可终止http server服务\n\n";
                echo "\n";
                break;
            }
        default:
            {
                echo "------------欢迎使用serverManager------------\n";
                echo "有关某个命令的详细信息，请键入 help 命令:\n\n";
                echo "1、php serverManager start help 查看详细信息!\n\n";
                echo "2、php serverManager stop help 查看详细信息!\n\n";
            }
    }
}

function startServer($server)
{
    opCacheClear();
    global $argv;
    switch (strtolower($server)) {
        case 'http':
            {
                $path = START_DIR_ROOT . '/protocol/http';
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
                $config_file = $path . '/config.php';
                if (!file_exists($config_file)) {
                    copy(SCORE_DIR_ROOT . '/score/Http/config.php', $config_file);
                }

                $event_server_file = $path . '/HttpServer.php';
                if (!file_exists($event_server_file)) {
                    copy(SCORE_DIR_ROOT . '/score/EventServer/HttpServer.php', $event_server_file);
                }

                $config = include $config_file;

                if (isset($argv[3]) && ($argv[3] == '-d' || $argv[3] == '-D')) {

                    $config['setting']['daemonize'] = true;
                }

                $http = new \protocol\http\HttpServer($config);
                $http->start();
                break;
            }
        default:
            {
                help($command = 'help');
            }
    }
    return;
}


