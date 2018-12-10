<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/7
 * Time: 10:35
 */

namespace Framework\SwServer\Timer;

use Framework\SwServer\ServerManager;
use Framework\Core\error\CustomerError;

class TimerCallbak
{
    public static function loop($micSeconds = 60 * 1000,callable $callback, $args = [])
    {
        $newCallBack = function (...$args) use ($callback) {
            try {
                call_user_func($callback, ...$args);
            } catch (\Throwable $e) {
                CustomerError::writeErrorLog($e);
            }
        };
        ServerManager::getSwooleServer()->tick($micSeconds, $newCallBack, $args);
   }

    public static function delay($micSeconds = 60 * 1000,callable $callback, $args = [])
    {
        $newCallBack = function (...$args) use ($callback) {
            try {
                call_user_func($callback, ...$args);
            } catch (\Throwable $e) {
                CustomerError::writeErrorLog($e);
            }
        };
        ServerManager::getSwooleServer()->after($micSeconds, $newCallBack, $args);
    }

    public static function clear($timerId){
        ServerManager::getSwooleServer()->clearTimer($timerId);
    }

}