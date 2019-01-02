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

class BaseApplication extends BaseObject
{
    public $fd;
    public $coroutine_id;
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->coroutine_id = CoroutineManager::getInstance()->getCoroutineId();
        WST::getInstance()->coroutine_id = $this->coroutine_id;
        WST::getInstance()->fd = $this->fd;
        $this->preInit();
        $this->setApp();

    }

    public function preInit()
    {
        $this->setTimeZone($this->config['timeZone']);
        (isset($this->config['log']) && $this->config['log']) && Log::getInstance()->setConfig($this->config['log']);
        $this->setErrorObject();
        $this->registerErrorHandler();
        $this->initComponents();
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


    public function ping(string $operate)
    {
        if (strtolower($operate) == 'ping') {
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
        WST::getInstance()->destroy();
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



    use \Framework\Traits\ContainerTrait,\Framework\Traits\ComponentTrait, \Framework\Traits\ServerTrait;
}