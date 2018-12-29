<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:30
 */

namespace Framework\SwServer;

use Framework\SwServer\Base\Objects;

abstract class BaseObject implements Objects
{


    public static function className()
    {
        return get_called_class();
    }


    public function __get($name)
    {
        if($this->$name){
            return $this->$name;
        }
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

    }


    public function __isset($name)
    {
        if (isset($this->$name)) {
            return true;
        }
        return false;
    }
}