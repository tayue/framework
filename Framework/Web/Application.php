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
use Framework\Tool\PluginManager;

class Application extends \Framework\Base\Application
{
    public function run($config)
    {
        Db::setConfig($config['components']['db']['config']);
        if (!$config['is_swoole_http_server']) {
            Route::parseRouteUrl();
        }


    }


}