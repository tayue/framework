<?php
/**
 * 方法回调超时抛异常
 * User: hdeng
 * Date: 2018/12/7
 * Time: 9:05
 */

namespace Framework\SwServer\Component;

use \Swoole\Process;

class Invoker
{
    public static function exec(callable $callable,$timeOut = 100 * 1000,$params)
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            Process::alarm(-1);
            throw new \RuntimeException('func timeout');
        });
        try
        {
            Process::alarm($timeOut);
            $ret = call_user_func($callable,...$params);
            Process::alarm(-1);
            return $ret;
        }
        catch(\Throwable $throwable)
        {
            throw $throwable;
        }
    }
}