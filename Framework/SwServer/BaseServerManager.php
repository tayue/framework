<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 17:48
 */

namespace Framework\SwServer;
use Framework\Framework;
use Swoole\Coroutine as SwCoroutine;
use Framework\SwServer\Sw;

abstract class BaseServerManager
{
    public $process_name = 'Tayue Server Framwork';
    public static $pidFile;
    public static $server;
    public $swoole_server;

    /**
     * 设置进程的名称
     * @param $name
     */
    public function setProcessName($name)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                @swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__ . " failed. require cli_set_process_title or swoole_set_process_name.");
            }
        }
        $this->process_name=$name;
    }

    public function getProcessName(){
        return $this->process_name;
    }

    public static function getSwooleServer(){
        return self::$server;
    }

    /**
     * isWorkerProcess 进程是否是worker进程
     * @param    $worker_id
     * @return   boolean
     */
    public static function isWorkerProcess() {
        return (!self::isTaskProcess()) ? true : false;
    }

    /**
     * isTaskProcess 进程是否是task进程
     * @param    $worker_id
     * @return   boolean
     */
    public static function isTaskProcess() {
        $server = self::getSwooleServer();
        if(property_exists($server, 'taskworker')) {
            return $server->taskworker;
        }
        throw new \Exception("not found task process,may be you use it before workerStart()", 1);
    }


    /**
     * Whether it is coroutine context
     *
     * @return bool
     */
    public static function isCoContext(): bool
    {
        return SwCoroutine::getuid() > 0;
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

    /**
     * serviceType 获取当前主服务器使用的协议
     * @return   mixed
     */
    public static function getServiceProtocol() {
        // websocket
        if(static::$server instanceof \Swoole\WebSocket\Server) {
            return SWOOLEFY_WEBSOCKET;
        }else if(static::$server instanceof \Swoole\Http\Server) {
            return SWOOLEFY_HTTP;
        }else if(static::$server instanceof \Swoole\Server) {
            if(self::$swoole_socket_type == SWOOLE_SOCK_UDP) {
                return SWOOLEFY_UDP;
            }
            return SWOOLEFY_TCP;
        }
        return false;

    }




}