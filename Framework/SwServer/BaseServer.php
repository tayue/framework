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
use Framework\SwServer\Process\Interfaces\ProcessMessageInterface;
use Framework\Tool\PluginManager;
use Framework\SwServer\Table\TableManager;
use Swoole\Runtime;

abstract class BaseServer implements Protocol
{
    const DEFAULT_PORT = 9501;
    const DEFAULT_HOST = '0.0.0.0';
    /**
     * $config
     * @var null
     */
    public $config = [
    ];
    public $default_setting = [
        'reactor_num' => 1,
        'worker_num' => 4,
        'max_request' => 1000,
        'task_worker_num' => 4,
        'task_tmpdir' => '/dev/shm',
        'daemonize' => 0
    ];
    public $setting = [];
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

    /**
     * $isEnableRuntimeCoroutine 是否开启运行时协程
     * @var boolean
     */
    public static $isEnableRuntimeCoroutine = false;

    /**
     * $_tasks 实时内存表保存数据,所有worker共享
     * @var null
     */
    public static $_table_tasks = [
        // 循环定时器内存表
        'table_ticker' => [
            // 每个内存表建立的行数
            'size' => 4,
            // 字段
            'fields' => [
                ['tick_tasks', 'string', 8096]
            ]
        ],
        // 一次性定时器内存表
        'table_after' => [
            'size' => 4,
            'fields' => [
                ['after_tasks', 'string', 8096]
            ]
        ],
        //$_workers_pids 记录映射进程worker_pid和worker_id的关系
        'table_workers_pid' => [
            'size' => 1,
            'fields' => [
                ['workers_pid', 'string', 512]
            ]
        ],
    ];

    /**
     * setSwooleSockType 设置socket的类型
     */
    protected function setSwooleSockType()
    {
        if (isset($this->setting['swoole_process_mode']) && $this->setting['swoole_process_mode'] == SWOOLE_BASE) {
            self::$swoole_process_mode = SWOOLE_BASE;
        }

        if (self::isUseSsl()) {
            self::$swoole_socket_type = SWOOLE_SOCK_TCP | SWOOLE_SSL;
        }
        return;
    }


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
        $this->config = $config;
        if (isset($config['server']['setting']) && $config['server']['setting']) {
            $this->setting = array_merge($this->default_setting, $config['server']['setting']);
        } else {
            $this->setting = $this->default_setting;
        }
        // set timeZone
        $this->setTimeZone();
        $this->createTables();
        self::checkSapiEnv();
        self::enableCoroutine();
    }

    /**
     * checkSapiEnv 判断是否是cli模式启动
     * @return void
     */
    public static function checkSapiEnv()
    {
        // Only for cli.
        if (php_sapi_name() != 'cli') {
            throw new \Exception("only run in command line mode \n", 1);
        }
    }

    /**
     * enableCoroutine
     * @return
     */
    public static function enableCoroutine()
    {
        if (version_compare(swoole_version(), '4.0.0', '>')) {
            //从4.1.0版本开始支持了对PHP原生Redis、PDO、MySQLi协程化的支持。可使用Swoole\Runtime::enableCorotuine()将普通的同步阻塞Redis、PDO、MySQLi操作变为协程调度的异步非阻塞IO
            if (version_compare(swoole_version(), '4.1.0', '>')){
                echo "\e[32m" . str_pad("Swoole\Runtime::enableCorotuine ", 20, ' ', STR_PAD_RIGHT) . "\e[34m" . "enableCorotuine" . "\e[0m\n";
                self::$isEnableRuntimeCoroutine=true;
                Runtime::enableCoroutine();
            }else{
                echo "\e[32m" . str_pad("Swoole\Runtime::enableStrictMode ", 20, ' ', STR_PAD_RIGHT) . "\e[34m" . "enableStrictMode" . "\e[0m\n";
                Runtime::enableStrictMode();
            }
            self::$isEnableCoroutine = true;
            return;
        } else {
            // 低于4.0版本不能使用协程
            self::$isEnableCoroutine = false;
            return;
        }

    }

    /**
     * isUseSsl 判断是否使用ssl加密
     * @return   boolean
     */
    protected function isUseSsl()
    {
        if (isset($this->config['ssl_cert_file']) && isset($this->config['ssl_key_file'])) {
            return true;
        }
        return false;
    }

    /**
     * createTables 默认创建定时器任务的内存表
     * @return  void
     */
    public function createTables()
    {
        if (!isset($this->config['table']) || !is_array($this->config['table'])) {
            $this->config['table'] = [];
        }
        if (isset($this->config['open_table_tick_task']) && $this->config['open_table_tick_task'] == true) {
            $tables = array_merge(self::$_table_tasks, $this->config['table']);
        } else {
            $tables = $this->config['table'];
        }
        //create table
        TableManager::createTable($tables);
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

    /**
     * isEnableRuntimeCoroutine
     * @return boolean
     */
    public static function canEnableRuntimeCoroutine()
    {
        return self::$isEnableRuntimeCoroutine;
    }

    public function setTimeZone()
    {
        // 默认
        $timezone = 'PRC';
        if (isset($this->config['time_zone'])) {
            $timezone = $this->config['time_zone'];
        }
        date_default_timezone_set($timezone);
        return;
    }


    function onTask(\swoole_server $server, $taskId, $fromWorkerId, $taskObj)
    {
        if ($taskObj) {
            $taskObj = \unserialize($taskObj);
            if (is_array($taskObj)) {
                list($classData, $params) = $taskObj;
                list($class, $action) = $classData;
                $class = new $class();
                $class->$action(...$params);
                unset($class);
                unset($taskObj);
            }
        }
        //任务投递结束返回worker进程
        return "TaskId:{$taskId},FromWorkerId:{$fromWorkerId},Finish!";
    }

    public function onPipeMessage(\swoole_server $server, $src_worker_id, $taskObj)
    {
        //processAsync
        if ($taskObj) {
            $taskObj = \unserialize($taskObj);
            $ref = new \ReflectionClass(get_class($taskObj));
            if ($ref->implementsInterface(ProcessMessageInterface::class)) {
                try {
                    PluginManager::getInstance()->triggerHook($taskObj->getHook(), $taskObj->getMessageData(), $taskObj->getMessageParams());
                } catch (\Throwable $throwable) {
                    echo $throwable->getMessage();
                }
                return;
            }
            unset($ref, $taskObj);
        }
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
