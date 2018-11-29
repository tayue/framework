<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 15:17
 */

namespace Framework\SwServer;

use Framework\SwServer\Protocol\WebServer;
use Framework\SwServer\BaseServerManager;
use Framework\SwServer\Sw;


class ServerManager extends BaseServerManager
{
    use \Framework\Traits\SingletonTrait;
    const TYPE_SERVER = 'SERVER';
    const TYPE_WEB_SERVER = 'WEB_SERVER';
    const TYPE_WEB_SOCKET_SERVER = 'WEB_SOCKET_SERVER';
    public $protocol;
    public $config;


    public static $isWebServer = false;
    public static $serviceType;

    private function __construct()
    {

    }

    public function setProtocol(\Framework\SwServer\Protocol\Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function getProtocol()
    {
       return  $this->protocol ;
    }


    public function createServer($config)
    {
        $this->config = $config;
        if (isset($this->config['main_server']) && $this->config['main_server']) {
            self::$serviceType = $this->config['main_server'];
        } else {
            self::$serviceType = self::TYPE_WEB_SERVER;
        }

        switch (self::$serviceType) {
            case self::TYPE_SERVER;
                break;
            case self::TYPE_WEB_SERVER;
                self::$isWebServer = true;
                $this->protocol = new WebServer($this->config);
                $this->swoole_server = $this->protocol->createServer();
                self::$server = $this->swoole_server;
                break;
            case self::TYPE_WEB_SOCKET_SERVER;
                break;
        }
        Sw::$server=self::$server;
        $this->registerDefaultEventCallback();
    }


    public function registerDefaultEventCallback()
    {
        $this->swoole_server->on('ManagerStart', function ($serv) {
            $this->setProcessName($this->getProcessName() . ': manager');
        });
        if (self::$isWebServer) {
            $this->swoole_server->on('Request', array($this->protocol, 'onRequest'));
        } else {
            $this->swoole_server->on('Receive', array($this->protocol, 'onReceive'));
        }
        $this->swoole_server->on('Start', array($this, 'onMasterStart'));
        $this->swoole_server->on('Shutdown', array($this, 'onMasterStop'));
        $this->swoole_server->on('ManagerStop', array($this, 'onManagerStop'));

        $this->swoole_server->on('WorkerStart', array($this, 'onWorkerStart'));
        if (is_callable(array($this->protocol, 'WorkerStop')))
        {
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
        $this->setProcessName($this->getProcessName() . ': master -host=' . $this->protocol->host . ' -port=' . $this->protocol->port);
        if (!empty($this->config['pid_file'])) {
            file_put_contents($this->config['pid_file'], $serv->master_pid);
        }
        self::$pidFile = $serv->master_pid;
        if (method_exists($this->protocol, 'onMasterStart')) {
            $this->protocol->onMasterStart($serv);
        }

        var_dump(self::$pidFile);
    }

    function onMasterStop($serv)
    {
        if (!empty($this->config['pid_file'])) {
            unlink(self::$pidFile);
        }
        if (method_exists($this->protocol, 'onMasterStop')) {
            $this->protocol->onMasterStop($serv);
        }
    }

    function onWorkerStart($serv, $worker_id)
    {
        self::clearCache();

        if ($worker_id >= $serv->setting['worker_num']) {
            $this->setProcessName($this->getProcessName() . ': task');
        } else {
            $this->setProcessName($this->getProcessName() . ': worker');
        }
        if (method_exists($this->protocol, 'onStart')) {
            $this->protocol->onStart($serv, $worker_id);
        }
        if (method_exists($this->protocol, 'onWorkerStart')) {
            $this->protocol->onWorkerStart($serv, $worker_id);
        }
    }

    function getsw(){
        return $this->swoole_server;
    }


}