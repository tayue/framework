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

class ServerManager extends BaseServerManager
{
    public $swoole_server;
    const TYPE_SERVER = 'SERVER';
    const TYPE_WEB_SERVER = 'WEB_SERVER';
    const TYPE_WEB_SOCKET_SERVER = 'WEB_SOCKET_SERVER';
    public $protocol;
    public static $isWebServer = false;
    public static $serviceType;

    public function setProtocol(\Framework\SwServer\Protocol\Protocol $protocol)
    {
        $this->protocol = $protocol;

    }

    public function createServer($config)
    {
        if (isset($config['main_server']) && $config['main_server']) {
            self::$serviceType = $config['main_server'];
        } else {
            self::$serviceType = self::TYPE_WEB_SERVER;
        }

        switch (self::$serviceType) {
            case self::TYPE_SERVER;
                break;
            case self::TYPE_WEB_SERVER;
                self::$isWebServer = true;
                $this->protocol = new WebServer($config);
                $this->swoole_server = $this->protocol->createServer();
                break;
            case self::TYPE_WEB_SOCKET_SERVER;
                break;
        }
        $this->registerDefaultEventCallback();

    }


    public function registerDefaultEventCallback()
    {   echo "___________\r\n";
        var_dump($this->swoole_server);
        $this->swoole_server->on('Start', array($this, 'onMasterStart'));

//        if (is_callable(array($this->protocol, 'onConnect'))) {
//            $this->sw->on('Connect', array($this->protocol, 'onConnect'));
//        }

    }

    function onMasterStart($serv)
    {    echo "@@@@@@@@@@\r\n";
        $this->setProcessName($this->getProcessName() . ': master -host=' . $this->protocol->host . ' -port=' . $this->protocol->port);
        if (!empty($this->runtimeSetting['pid_file'])) {
            file_put_contents(self::$pidFile, $serv->master_pid);
        }
        if (method_exists($this->protocol, 'onMasterStart')) {
            $this->protocol->onMasterStart($serv);
        }
    }


    public function start()
    {

    }





}