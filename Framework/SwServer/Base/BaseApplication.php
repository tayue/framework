<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/27
 * Time: 9:46
 */

namespace Framework\SwServer\Base;

use Framework\Di\ServerContainer;
use Framework\Core\error\CustomerError;
use Framework\Core\log\Log;
use Framework\SwServer\WebSocket\WST;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\ServerManager;
use Framework\Core\Route;
use Framework\Core\Db;

class BaseApplication extends BaseObject
{
    public $fd;
    public $coroutine_id;
    public $header = null;


    public function __construct()
    {
        $this->preInit();
    }

    public function init()
    {
        $this->coroutine_id = CoroutineManager::getInstance()->getCoroutineId();
        WST::getInstance()->coroutine_id = $this->coroutine_id;
        WST::getInstance()->fd = $this->fd;
        $this->setTimeZone(ServerManager::$config['timeZone']);
        (isset(ServerManager::$config['log']) && ServerManager::$config['log']) && Log::getInstance()->setConfig(ServerManager::$config['log']);
        Db::setConfig(ServerManager::$config['components']['db']['config']);
        $this->setApp();
        WST::configure(WST::$app[$this->coroutine_id], ServerManager::$config);
        $this->initComponents();
        $this->initServices();
    }

    public function preInit()
    {
        $this->setErrorObject();
        $this->registerErrorHandler();
    }

    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }

    public function setErrorObject()
    {
        if (!ServerContainer::getInstance()->get('CustomerError')) {
            $ce = new CustomerError();
            if (class_exists(get_class($ce))) {
                ServerContainer::getInstance()->set('CustomerError', $ce);
            }
        }
    }

    protected function registerErrorHandler()
    {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $CustomerErrorObject = ServerContainer::getInstance()->get('CustomerError');
        $methodgGeneralError = array($CustomerErrorObject, 'generalError');
        if (is_callable($methodgGeneralError, true)) {
            set_error_handler([get_class($CustomerErrorObject), 'generalError']);
        }
        $methodFatalError = array($CustomerErrorObject, 'fatalError');
        if (is_callable($methodFatalError, true)) {
            register_shutdown_function([get_class($CustomerErrorObject), 'fatalError']);
        }
    }

    /**
     * ping 心跳检测
     * @return
     */
    public function ping($operate = 'null')
    {
        if (isset($this->header['ping']) && $this->header['ping'] == 'ping') {
            return true;
        } else if (strtolower($operate) == 'ping') {
            return true;
        }
        return false;
    }


    public function parseRoute($messageData)
    {
        // worker进程
        if ($this->isWorkerProcess()) {
            $recv = array_values(json_decode($messageData, true));
            if (is_array($recv) && count($recv) == 3) {
                list($service, $operate, $params) = $recv;
            }
            if ($this->ping($operate)) {
                $data = 'pong';
                ServerManager::getSwooleServer()->push($this->fd, $data, $opcode = 1, $finish = true);
                return;
            }

            if ($service && $operate) {
                $callable = [$service, $operate];
            }

        } else {
            // 任务task进程
            list($callable, $params) = $messageData;
        }
        // 控制器实例
        if ($callable && $params) {
            Route::parseServiceMessageRouteUrl($callable, $params);
        }
        ServerManager::getInstance()->removeApp();
    }

    public function parseTcpRoute($receiveData)
    {
        // worker进程
        if ($this->isWorkerProcess()) {
            list($header, $body) = $receiveData;
            $header && $this->header = $header;
            $body=array_values($body);
            if (is_array($body) && count($body) == 3) {
                list($service, $operate, $params) = $body;
            }

            if ($this->ping()) {
                $args = ['pong', $this->header];
                $data = \Framework\SwServer\Protocol\TcpServer::pack($args);
                ServerManager::getSwooleServer()->send($this->fd, $data);
                return;
            }
            if ($service && $operate) {
                $callable = [$service, $operate];
            }

        } else {
            // 任务task进程
            list($callable, $params) = $receiveData;
        }
        // 控制器实例
        if ($callable && $params) {
            Route::parseServiceMessageRouteUrl($callable, $params);
        }
        ServerManager::getInstance()->removeApp();
   }

    public function setApp()
    {
        $cid = -1;
        if ($this->coroutine_id) {
            $cid = $this->coroutine_id;
        } else {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
        }
        if ($cid) {
            WST::$app[$cid] = $this;
        } else {
            WST::$app = $this;
        }
    }

    public function __get($name)
    {
        if (isset($this->_components[$name])) {
            $componentObject = $this->getComponent($name);
            if ($componentObject) {
                return $componentObject;
            } else {
                $this->clearComponent($name);
                return false;
            }
        } else if (isset($this->_services[$name])) {
            $serviceObject = $this->getService($name);
            if ($serviceObject) {
                return $serviceObject;
            } else {
                $this->clearService($name);
                return false;
            }
        }
        parent::__get($name);
    }

    use \Framework\Traits\ComponentTrait, \Framework\Traits\ServerTrait, \Framework\Traits\ServiceTrait, \Framework\Traits\ContainerTrait;
}