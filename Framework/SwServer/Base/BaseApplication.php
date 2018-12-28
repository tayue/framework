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
use Framework\SwServer\Common\ProtocolCommon;

class BaseApplication extends BaseObjects
{
    public $fd;
    public static $routeCacheFileMap;
    public $coroutine_id;

    public function __construct($config)
    {
        $this->preInit($config);
        WST::$app = $this;
        $this->coroutine_id = CoroutineManager::getInstance()->getCoroutineId();

    }

    public function preInit($config)
    {
        (isset($config['log']) && $config['log']) && Log::getInstance()->setConfig($config['log']);
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


    public function ping(string $operate) {
        if(strtolower($operate) == 'ping') {
            return true;
        }
        return false;
    }

    /**
     * checkClass 检查请求实例文件是否存在
     * @param  string  $class
     * @return boolean
     */
    public function checkClass($class) {
        $path = str_replace('\\', '/', $class);
        $path = trim($path, '/');
        $file = ROOT_PATH.DIRECTORY_SEPARATOR.$path.'.php';
        if(is_file($file)) {
            self::$routeCacheFileMap[$class] = true;
            return true;
        }
        return false;
    }

    public function parseRoute($messageData){
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
            $this->parseServiceMessageRouteUrl($callable, $params);
        }
    }

    public function parseServiceMessageRouteUrl($callable, $params)
    {
        try {
            $errorMessage = '';
            list($service, $operate) = $callable;
            $service = str_replace('/', '\\', $service);
            $serviceInstance = new $service();
            $serviceInstance->mixedParams = $params;
            $isExists = $this->checkClass($service);
            if ($isExists) {
                if (method_exists($serviceInstance, $operate)) {
                    $serviceInstance->$operate($params);
                } else {
                    $errorMessage = "Service:{$service},Operate:{$operate},Is Not Found !!";
                    ProtocolCommon::sender($this->fd, $errorMessage);
                }

            } else {
                throw new \Exception("404");
                $errorMessage = "Service:{$service} Class Is Not Found !!";
                ProtocolCommon::sender($this->fd, $errorMessage, 0);
            }

        } catch (\Exception $e) {
            ProtocolCommon::sender($this->fd, $e->getMessage(), $e->getCode());
            throw new \Exception($e->getMessage(), 1);
        } catch (\Throwable $t) {
            ProtocolCommon::sender($this->fd, $t->getMessage(), $t->getCode());
            throw new \Exception($t->getMessage(), 1);
        }

    }


    use \Framework\Traits\ComponentTrait,\Framework\Traits\ServerTrait;
}