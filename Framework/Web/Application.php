<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:18
 */

namespace Framework\Web;
use Framework\Core\Db;
use Framework\Core\Route;


class Application extends \Framework\Base\Application
{
    public function run($config)
    {
        Db::setConfig($config['components']['db']['config']);
        Route::parseRouteUrl();
    }
}