<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 15:17
 */

namespace Framework\SwServer;


use Framework\SwServer\Protocol\WebServer;
use Framework\SwServer\Protocol\WebSocketServer;
use Framework\Tool\PluginManager;
use Framework\Core\log\Log;
use Framework\SwServer\Crontab\Crontab;
use App\Crontab\TaskOne;
use Framework\SwServer\Process\ProcessManager;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;
use Framework\SwServer\Protocol\TcpServer;
use Framework\Traits\SingletonTrait;
use Framework\Traits\AppTrait;
use Framework\SwServer\Protocol\Protocol;
use Framework\SwServer\Crontab\CronRunner;
use Framework\SwServer\Event\EventManager;

class ServerManager extends BaseServerManager
{
    use SingletonTrait, AppTrait;
    const TYPE_SERVER = 'SERVER';
    const TYPE_WEB_SERVER = 'WEB_SERVER';
    const TYPE_WEB_SOCKET_SERVER = 'WEB_SOCKET_SERVER';
    public $protocol;
    public static $isWebServer = false;
    public static $isWebSocketServer = false;
    public static $isEnableRuntimeCoroutine = false;
    public static $serviceType;
    public static $eventManager;
    public static $tables = [];
    public static $serverApp;


    private function __construct()
    {
        self::$eventManager=new EventManager(); //全局的事件管理器
        //注册consul 注册服务事件
        self::$eventManager->attach("consulServiceRegister","App\Listener\RegisterConsulServiceListener");
        //销毁consul 销毁服务事件
        self::$eventManager->attach("consulServiceDestroy","App\Listener\DestroyConsulServiceListener");
    }

    public function setProtocol(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }


    public function createServer($config)
    {
        //注册进程任务
        PluginManager::getInstance()->registerClassHook('ProcessAsyncTask', 'Framework/SwServer/Task/ProcessAsyncTask', 'onPipeMessage');
        $this->crontabTasks();
        $this->setErrorObject();
        $this->registerErrorHandler();
        self::$config = $config;
        if (isset(self::$config['server']['server_type']) && self::$config['server']['server_type']) {
            self::$serviceType = self::$config['server']['server_type'];
        } else {
            self::$serviceType = self::TYPE_WEB_SERVER;
        }

        switch (self::$serviceType) {
            case self::TYPE_SERVER;
                self::$isWebServer = false;
                $this->protocol = new TcpServer(self::$config);
                break;
            case self::TYPE_WEB_SERVER;
                self::$isWebServer = true;
                $this->protocol = new WebServer(self::$config);
                break;
            case self::TYPE_WEB_SOCKET_SERVER;
                self::$isWebServer = true;
                self::$isWebSocketServer = true;
                $this->protocol = new WebSocketServer(self::$config);
                break;
        }
        $this->swoole_server = $this->protocol->createServer();
        self::$isEnableRuntimeCoroutine = $this->protocol::canEnableRuntimeCoroutine();
        self::$server = $this->swoole_server;
        Sw::$server = self::$server;
        $this->registerDefaultEventCallback();
        ProcessManager::getInstance()->addProcess('CronRunner', CronRunner::class, true, Crontab::getInstance()->getTasks());
        (isset(self::$config['log']) && self::$config['log']) && Log::getInstance()->setConfig(self::$config['log']);

    }


    public function registerDefaultEventCallback()
    {
        if (self::$isWebServer) {
            $this->swoole_server->on('Request', array($this->protocol, 'onRequest'));
        } else {
            $this->swoole_server->on('Receive', array($this->protocol, 'onReceive'));
        }
        if (self::$isWebSocketServer) {
            $this->swoole_server->on('Message', array($this->protocol, 'onMessage'));
        }
        $this->swoole_server->on('Start', array($this, 'onMasterStart'));
        $this->swoole_server->on('Shutdown', array($this, 'onMasterStop'));
        $this->swoole_server->on('ManagerStart', function ($serv) {
            $this->setProcessName($this->getProcessName() . ': manager');
        });
        $this->swoole_server->on('ManagerStop', array($this, 'onManagerStop'));
        $this->swoole_server->on('WorkerStart', array($this, 'onWorkerStart'));
        if (is_callable(array($this->protocol, 'WorkerStop'))) {
            $this->swoole_server->on('WorkerStop', array($this->protocol, 'WorkerStop'));
        }
        if (is_callable(array($this->protocol, 'onConnect'))) {
            $this->swoole_server->on('Connect', array($this->protocol, 'onConnect'));
        }
        if (is_callable(array($this->protocol, 'onClose'))) {
            $this->swoole_server->on('Close', array($this->protocol, 'onClose'));
        }
        if (is_callable(array($this->protocol, 'onTask'))) {
            $this->swoole_server->on('Task', array($this->protocol, 'onTask'));
            $this->swoole_server->on('Finish', array($this->protocol, 'onFinish'));
        }

        if (is_callable(array($this->protocol, 'onPipeMessage'))) {
            $this->swoole_server->on('pipeMessage', array($this->protocol, 'onPipeMessage'));
        }
    }

    public function start()
    {
        $this->swoole_server->start();
    }

    function onManagerStop()
    {

    }

    function onMasterStart($serv)
    {
        $this->setProcessName($this->getProcessName() . ': master -host=' . self::$config['server']['listen_address'] . ' -port=' . self::$config['server']['listen_port']);
        if (!empty(self::$config['server']['pid_file'])) {
            file_put_contents(self::$config['server']['pid_file'], $serv->master_pid);
        }
        self::$pidFile = $serv->master_pid;
        if (method_exists($this->protocol, 'onMasterStart')) {
            $this->protocol->onMasterStart($serv);
        }
        ServerManager::$eventManager->trigger("consulServiceRegister");
    }

    function onMasterStop($serv)
    {
        if (!empty(self::$config['server']['pid_file'])) {
            @unlink(self::$pidFile);
        }
        ServerManager::$eventManager->trigger("consulServiceDestroy");
        if (method_exists($this->protocol, 'onMasterStop')) {
            $this->protocol->onMasterStop($serv);
        }
    }

    function onWorkerStart($server, $worker_id)
    {
        echo "" . date("Y-m-d H:i:s") . " onWorkerStart\r\n";
        self::clearCache();
        // 记录主进程加载的公共files,worker重启不会在加载的
        self::getIncludeFiles();
        // 启动时提前加载文件
        self::startInclude();
        // 记录worker的进程worker_pid与worker_id的映射
        self::setWorkersPid($worker_id, $server->worker_pid);
        // 设置worker工作的进程组
        self::setWorkerUserGroup(self::$config['server']['www_user']);
        if ($worker_id >= $server->setting['worker_num']) {
            $this->setProcessName($this->getProcessName() . ': task');
        } else {
            $this->setProcessName($this->getProcessName() . ': worker');
            MysqlPoolManager::getInstance(self::$config['mysql_pool'])->clearSpaceResources();
            RedisPoolManager::getInstance(self::$config['redis_pool'])->clearSpaceResources();
        }
        if (method_exists($this->protocol, 'onStart')) {
            $this->protocol->onStart($server, $worker_id);
        }
        if (method_exists($this->protocol, 'onWorkerStart')) {
            $this->protocol->onWorkerStart($server, $worker_id);
        }
    }

    public function crontabTasks()
    {
        // 开始一个定时任务计划
        $time = date("Y-m-d H:i:s");
        Crontab::getInstance()->addTask(TaskOne::class, 'run', ['date' => $time]);
    }


    public static function getModule()
    {
        return self::getApp()->current_module;
    }

    public static function getController()
    {
        return self::getApp()->current_controller;
    }

    public static function getAction()
    {
        return self::getApp()->current_action;
    }

    public static function getProjectType()
    {
        return self::getApp()->project_type;
    }


}