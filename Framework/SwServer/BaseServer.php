<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 15:54
 */

namespace Framework\SwServer;

use Framework\Core\Exception;
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

    /**
     * $isEnableCoroutine 是否启用协程
     * @var boolean
     */
    public static $isEnableCoroutine = false;

    public $host = '';

    public $port = '';

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
    public static function getStats()
    {
        return self::$server->stats();
    }

    /**
     * isEnableCoroutine
     * @return boolean
     */
    public static function canEnableCoroutine()
    {
        return self::$isEnableCoroutine;
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

    function task($task, $dstWorkerId = -1, $callback = null)
    {
        self::$server->task($task, $dstWorkerId = -1, $callback);
    }

    function onTask(\swoole_server $server, $taskId, $fromWorkerId, $taskObj)
    {
        if ($taskObj) {
            $taskObj = \Swoole\Serialize::unpack($taskObj);
            if (is_array($taskObj)) {
                list($classData, $params) = $taskObj;
                list($class, $action) = $classData;
                $class = new $class();
                $class->$action($params);
                unset($class);
                unset($taskObj);
            }
        }              //任务投递结束返回worker进程
        return "TaskId:{$taskId},FromWorkerId:{$fromWorkerId},Finish!";
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