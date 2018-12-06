<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:18
 */

namespace Framework\Web;


use Framework\Core\Db;

use Framework\Core\Route;
use Framework\Tool\Log;
use Framework\SwServer\ServerManager;
use Framework\Tool\PluginManager;

class Application extends \Framework\Base\Application
{
    public function run($config)
    {
        //$this->registerErrorHandler();
        Db::setConfig($config['components']['db']['config']);
        Log::getInstance()->setConfig(['log_dir' => $config['log']['log_dir']]);
        Log::getInstance()->put("hello world", 'error');
        if (!$config['is_swoole_http_server']) {
            Route::parseRouteUrl();
        }else{//用swoole服务器启动
            //注册进程任务
            PluginManager::getInstance()->registerClassHook('ProcessAsyncTask', 'Framework/SwServer/Task/ProcessAsyncTask', 'onPipeMessage');
        }


    }


}