<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 17:48
 */

namespace Framework\SwServer;
use Swoole\Coroutine as SwCoroutine;
use Framework\SwServer\Table\TableManager;
use Framework\Di\ServerContainer;
use Framework\Core\error\CustomerError;

abstract class BaseServerManager
{
    public $process_name = 'Tayue Server Framwork';
    public static $pidFile;
    public static $server;
    public static $app;
    public $swoole_server;
    public static $config;


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

    /**
     * setWorkersPid 记录worker对应的进程worker_pid与worker_id的映射
     * @param    $worker_id
     * @param    $worker_pid
     */
    public static function setWorkersPid($worker_id, $worker_pid) {
        $workers_pid = self::getWorkersPid();
        $workers_pid[$worker_id] = $worker_pid;
        TableManager::set('table_workers_pid', 'workers_pid', ['workers_pid'=>json_encode($workers_pid)]);
    }

    /**
     * getWorkersPid 获取线上的实时的进程worker_pid与worker_id的映射
     * @return
     */
    public static function getWorkersPid() {
        return json_decode(TableManager::get('table_workers_pid', 'workers_pid', 'workers_pid'), true);
    }


    /**
     * setWorkerUserGroup 设置worker进程的工作组，默认是root
     * @param  string $worker_user
     */
    public static function setWorkerUserGroup($worker_user=null) {
        if(!isset(static::$config['user'])) {
            if($worker_user) {
                $userInfo = posix_getpwnam($worker_user);
                if($userInfo) {
                    posix_setuid($userInfo['uid']);
                    posix_setgid($userInfo['gid']);
                }
            }
        }
    }

    /**
     * getIncludeFiles 获取woker启动前已经加载的文件
     * @param   string $dir
     * @return   void
     */
    public static function getIncludeFiles($dir='Http') {

        if(isset(static::$config['log'])) {
            $path = static::$config['log']['log_dir'];
            $dir = strtolower($dir);
            $filePath = $path.DIRECTORY_SEPARATOR.$dir.'/includes.txt';
        }else {
            $dir = ucfirst($dir);
            $filePath = __DIR__.'/../'.$dir.'/includes.json';
        }
        $includes = get_included_files();
        if(is_file($filePath)) {
            @unlink($filePath);
        }
        @file_put_contents($filePath,var_export($includes,true));
        @chmod($filePath,0766);
    }


    /**
     * startInclude 设置需要在workerstart启动时加载的配置文件
     * @param  array  $includes
     * @return   void
     */
    public static function startInclude() {
        $includeFiles = isset(static::$config['include_files']) ? static::$config['include_files'] : [];
        if($includeFiles) {
            foreach($includeFiles as $filePath) {
                include_once $filePath;
            }
        }
    }

    public function setErrorObject($config=[])
    {
        if(!ServerContainer::getInstance()->get('CustomerError')){
            $ce=new CustomerError();
            if(class_exists(get_class($ce))){
                ServerContainer::getInstance()->set('CustomerError',$ce);
            }
        }
    }

    protected function registerErrorHandler()
    {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $CustomerErrorObject=ServerContainer::getInstance()->get('CustomerError');
        $methodgGeneralError = array($CustomerErrorObject, 'generalError');
        if(is_callable($methodgGeneralError,true)){
            set_error_handler([get_class($CustomerErrorObject),'generalError']);
        }
        $methodFatalError = array($CustomerErrorObject, 'fatalError');
        if(is_callable($methodFatalError,true)){
           register_shutdown_function([get_class($CustomerErrorObject),'fatalError']);
        }
    }

    function handleFatal()
    {
        $error = error_get_last();
        if (isset($error['type']))
        {
            switch ($error['type'])
            {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $message = $error['message'];
                    $file = $error['file'];
                    $line = $error['line'];
                    $log = "$message ($file:$line)\nStack trace:\n";
                    $trace = debug_backtrace();
                    foreach ($trace as $i => $t)
                    {
                        if (!isset($t['file']))
                        {
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line']))
                        {
                            $t['line'] = 0;
                        }
                        if (!isset($t['function']))
                        {
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object']))
                        {
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                    if (isset($_SERVER['REQUEST_URI']))
                    {
                        $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                    }
                    error_log($log);

                default:
                    break;
            }
        }
    }




}