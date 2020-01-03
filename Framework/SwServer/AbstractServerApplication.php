<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 17:11
 */

namespace Framework\SwServer;

use Framework\Di\ServerContainer;
use Framework\Core\error\CustomerError;
use Framework\Core\log\Log;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\Core\Db;
use Framework\SwServer\Base\BaseObject;
use Framework\SwServer\Pool\DiPool;
use Framework\Traits\ServerTrait;
use Framework\SwServer\Protocol\TcpServer;

abstract class AbstractServerApplication extends BaseObject
{
    public $coroutine_id;
    public $fd;
    public $header = null;

    public function __construct()
    {
        $this->preInit();
    }

    public function preInit()
    {
        $this->setErrorObject();
        $this->registerErrorHandler();
        $this->setTimeZone(ServerManager::$config['timeZone']);
        (isset(ServerManager::$config['log']) && ServerManager::$config['log']) && Log::getInstance()->setConfig(ServerManager::$config['log']);
        Db::setConfig(ServerManager::$config['components']['db']['config']);
        DiPool::getInstance();
    }

    public function init()
    {
        $this->coroutine_id = CoroutineManager::getInstance()->getCoroutineId();
        ServerManager::getInstance()->coroutine_id = $this->coroutine_id;
        $this->setApp();
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

    public function setApp()
    {
        $cid = -1;
        if ($this->coroutine_id) {
            $cid = $this->coroutine_id;
        } else {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
        }
        if ($cid) {
            ServerManager::$app[$cid] = $this;
        } else {
            ServerManager::$app = $this;
        }
    }

    public function parseUrl(\swoole_http_request $request, \swoole_http_response $response)
    {
        Route::parseSwooleRouteUrl($request, $response);
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
            if (!is_array($params) && $params) {
                $params = [$params];
            }
            $params = array_values($params);
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
    }


    public function parseTcpRoute($receiveData)
    {
        // worker进程
        if ($this->isWorkerProcess()) {
            list($header, $body) = $receiveData;
            $header && $this->header = $header;
            $body = array_values($body);
            if (is_array($body) && count($body) == 3) {
                list($service, $operate, $params) = $body;
            }
            if (!is_array($params) && $params) {
                $params = [$params];
            }
            $params = array_values($params);
            if ($this->ping()) {
                $args = ['pong', $this->header];
                $data = TcpServer::pack($args);
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
    }

    public function __get($name)
    {
        $res = DiPool::getInstance()->get($name);
        if ($res) {
            return $res;
        } else {
            if (isset(ServerManager::$config[$name])) {
                return ServerManager::$config[$name];
            } else {
                parent::__get($name);
            }
        }
    }

    use ServerTrait;
}
