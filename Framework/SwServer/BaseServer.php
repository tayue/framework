<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 15:54
 */

namespace Framework\SwServer;

use Framework\SwServer\Protocol\Protocol;

abstract class BaseServer implements Protocol
{
    /**
     * $config
     * @var null
     */
    public $config = [];

    protected $log;

    /**
     * $swoole_process_model swoole的进程模式，默认swoole_process
     * @var [type]
     */
    protected static $swoole_process_mode = SWOOLE_PROCESS;

    /**
     * $swoole_socket_type swoole的socket设置类型
     * @var [type]
     */
    protected static $swoole_socket_type = SWOOLE_SOCK_TCP;

    /**
     * $server swoole服务器是否是web
     * @var null
     */
    public static $isWebServer = false;

    /**
     * $server swoole服务器对象实例
     * @var null
     */
    public static $server;

    public $host='';

    public $port='';

    /**
     * 设置Logger
     * @param $log
     */
    public function setLogger($log)
    {
        $this->log = $log;
    }


    public function __construct($config)
    {
        // set timeZone
        self::setTimeZone();


    }

    /**
     * getStatus 获取swoole的状态信息
     * @return   array
     */
    public static function getStats() {
        return self::$server->stats();
    }

    public static function setTimeZone()
    {
        // 默认
        $timezone = 'PRC';
        if (isset(static::$config['time_zone'])) {
            $timezone = static::$config['time_zone'];
        }
        date_default_timezone_set($timezone);
        return;
    }




    /**
     * clearCache 清空字节缓存
     * @return  void
     */
    public static function clearCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    function task($task, $dstWorkerId = -1, $callback = null)
    {
        $this->server->task($task, $dstWorkerId = -1, $callback);
    }

    function onStart($server)
    {
        // TODO: Implement onStart() method.
    }

    function onConnect($server, $client_id, $from_id)
    {
        // TODO: Implement onConnect() method.
    }

    function onReceive($server, $client_id, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    function onClose($server, $client_id, $from_id)
    {
        // TODO: Implement onClose() method.
    }

    function onShutdown($server)
    {
        // TODO: Implement onShutdown() method.
    }
}