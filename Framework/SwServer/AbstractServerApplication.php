<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 17:11
 */

namespace Framework\SwServer;

use Framework\Core\Route;
use Framework\Di\ServerContainer;
use Framework\Core\error\CustomerError;
use Framework\Core\log\Log;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\Core\Db;
use Framework\SwServer\Base\BaseObject;
use Framework\SwServer\Pool\DiPool;

abstract class AbstractServerApplication extends BaseObject
{
    protected $coroutine_id;

    public function __construct()
    {
        $this->preInit();
    }

    public function preInit()
    {
        $this->setErrorObject();
        $this->registerErrorHandler();
        DiPool::getInstance();
    }

    public function init()
    {
        $this->coroutine_id = CoroutineManager::getInstance()->getCoroutineId();
        ServerManager::getInstance()->coroutine_id = $this->coroutine_id;
        $this->setTimeZone(ServerManager::$config['timeZone']);
        (isset(ServerManager::$config['log']) && ServerManager::$config['log']) && Log::getInstance()->setConfig(ServerManager::$config['log']);
        Db::setConfig(ServerManager::$config['components']['db']['config']);
        $this->setApp();
        ServerManager::configure(ServerManager::$app[$this->coroutine_id], ServerManager::$config);
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

    public function __get($name)
    {
        $res = DiPool::getInstance()->get($name);
        if ($res) {
            return $res;
        } else {
            parent::__get($name);
        }
    }
    use \Framework\Traits\ServerTrait;
}
