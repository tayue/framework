<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:21
 */

namespace Framework\Base;

use Framework\Di\Container;
use Framework\Di\ComponentLoader;
use Framework\Di\ServiceLoader;
use Framework\Framework;
use Framework\Di\ServerContainer;
use Framework\Core\error\CustomerError;
use Framework\Core\log\Log;

abstract class Application extends ComponentLoader
{
    public function __construct($config)
    {
        $this->preInit($config);
        Framework::$app = $this;
        Framework::$container = Container::getInstance();
        Framework::$service = ServiceLoader::getInstance(['services' => $config['services']]);
        parent::__construct($config);
    }

    public function preInit($config)
    {
       if (isset($config['timeZone']))
            $this->setTimeZone($config['timeZone']);
        (isset($config['log']) && $config['log']) && Log::getInstance()->setConfig($config['log']);
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


}