<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:19
 */

use Framework\SwServer\ServerManager;
use Framework\SwServer\Inotify\Daemon;

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
date_default_timezone_set('PRC');

function help($command)
{
    switch (strtolower($command . '-' . 'help')) {
        case 'start-help':
            {
                echo "------------serverManager启动服务命令------------\n";
                echo "1、执行php serverManager start 即可启动server服务\n\n";
                echo "\n";
                break;
            }
        case 'stop-help':
            {
                echo "------------serverManager终止服务命令------------\n";
                echo "1、执行php serverManager stop 即可终止server服务\n\n";
                echo "\n";
                break;
            }
        case 'reload-help':
            {
                echo "------------serverManager重载服务命令------------\n";
                echo "1、执行php serverManager reload 即可重载server服务\n\n";
                echo "\n";
                break;
            }
        default:
            {
                echo "------------欢迎使用serverManager------------\n";
                echo "有关某个命令的详细信息，请键入 help 命令:\n\n";
                echo "1、php serverManager help 查看详细信息!\n\n";
                echo "2、php serverManager help 查看详细信息!\n\n";
            }
    }
}

function startServer()
{
    global $config;
    opCacheClear();
    $sm = ServerManager::getInstance();
    $sm->createServer($config);
    showTag('Main Server Type', $config['server']['server_type']);
    showTag('Listen Address', $config['server']['listen_address']);
    showTag('Listen Port', $config['server']['listen_port']);
    $ips = swoole_get_local_ip();
    foreach ($ips as $eth => $val) {
        showTag('Ip@' . $eth, $val);
    }
    showTag('Worker Num', $config['server']['setting']['worker_num']);
    showTag('Task Worker Num', $config['server']['setting']['task_worker_num']);
    $user = $config['server']['www_user'];
    if (empty($user)) {
        $user = get_current_user();
    }
    showTag('Run At User', $user);
    $daemonize = $config['server']['setting']['daemonize'];
    if ($daemonize) {
        $daemonize = 'true';
    } else {
        $daemonize = 'false';
    }
    showTag('Daemonize', $daemonize);
    showTag('Swoole Version', phpversion('swoole'));
    showTag('Php Version', phpversion());
    $sm->start();
    return;
}

function reloadServer($param)
{
    global $config;
    $all = false;
    if ($param == 'all') {
        $all = true;
    }
    $pidFile = $config['server']['pid_file'];
    if (file_exists($pidFile)) {
        if (!$all) {
            $sig = SIGUSR2;
            showTag('reloadType', "only-task");
        } else {
            $sig = SIGUSR1;
            showTag('reloadType', "all-worker");
        }
        opCacheClear();
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "pid :{$pid} not exist \n";
            return;
        }
        swoole_process::kill($pid, $sig);
        echo "send server reload command at " . date("y-m-d H:i:s") . "\n";
    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
    }

}

function stopServer($param)
{
    global $config;
    $force = false;
    if ($param == 'force') {
        $force = true;
    }
    $pidFile = $config['server']['pid_file'];
    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "PID :{$pid} not exist \n";
            return false;
        }
        if ($force) {
            swoole_process::kill($pid, SIGKILL);
        } else {
            swoole_process::kill($pid);
        }
        //等待5秒
        $time = time();
        $flag = false;
        while (true) {
            usleep(1000);
            if (!swoole_process::kill($pid, 0)) {
                echo "server stop at " . date("y-m-d H:i:s") . "\n";
                if (is_file($pidFile)) {
                    @unlink($pidFile);
                }
                $flag = true;
                break;
            } else {
                if (time() - $time > 5) {
                    echo "stop server fail.try -f again \n";
                    break;
                }
            }
        }
        return $flag;
    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
        return false;
    }

}

function monitor($param)
{
    global $config;
    if ($param == '-d' || $param == '-D') {
        swoole_process::daemon(true, false);
    }
    $pid = posix_getpid();
    $monitor_port = $config['inotify']['monitorPort'];
    $monitor_pid_file = DATA_PATH . '/monitor' . $monitor_port . '.pid';
    @file_put_contents($monitor_pid_file, $pid);
    $monitor_process_name = (isset($config['monitorProcessName']) && !empty($config['monitorProcessName'])) ? $config['monitorProcessName'] : 'php-inotify-swoole-server';
    // 设置当前进程的名称
    cli_set_process_title($monitor_process_name . '-' . $monitor_port);
    // 创建进程服务实例
    $daemon = new Daemon($config);
    // 启动
    $daemon->run();

}

function showTag($name, $value)
{
    echo "\e[32m" . str_pad($name, 20, ' ', STR_PAD_RIGHT) . "\e[34m" . $value . "\e[0m\n";
}

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


function commandHandler()
{
    global $argv;
    $commandList = $argv;
    $paramCommand = '';
    array_shift($commandList);
    $mainCommand = array_shift($commandList);
    $commandList && $paramCommand = array_shift($commandList);
    if (isset($mainCommand) && $mainCommand != 'help') {
        switch ($mainCommand) {
            case "start":
                {
                    startServer();
                    break;
                }
            case 'stop':
                {
                    stopServer($paramCommand);
                    break;
                }
            case 'reload':
                {
                    reloadServer($paramCommand);
                    break;
                }
            case 'monitor':
                {
                    monitor($paramCommand);
                    break;
                }
            case 'help':
            default:
                {
                    help($mainCommand);
                }
        }
    } else {
        help($mainCommand);
    }
}

initCheck();
commandHandler();
