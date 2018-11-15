<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:21
 */

namespace Framework\Base;
use Framework\Di\Container;
use Framework\Di\ComponentLoader;
use Framework\Di\ServiceLoader;
use Framework\Framework;

abstract class Application extends ComponentLoader
{
    public function __construct($config)
    {
        $this->preInit($config);
        Framework::$app = $this;
        Framework::$container=Container::getInstance();
        Framework::$service = ServiceLoader::getInstance(['services'=>$config['services']]);
        parent::__construct($config);
   }

    public function preInit($config)
    {
      if(isset($config['timeZone']))
      $this->setTimeZone($config['timeZone']);

    }

    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }


}