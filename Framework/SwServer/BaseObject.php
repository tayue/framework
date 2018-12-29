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
        if (isset($this->_services[$name]) && !isset($this->$name)) {
            if (is_object($this->_singletons[$this->_services[$name]])) {
                return $this->_singletons[$this->_services[$name]];
            } else {
                $this->clearService($name);
                return false;
            }
        } else if ($this->_components[$name] && !isset($this->$name)) {
            if (is_object($this->_singletons[$this->_components[$name]])) {
                return $this->_singletons[$this->_components[$name]];
            } else {
                $this->clearComponent($name);
                return false;
            }
        }
        return $this->$name;
    }



    public function __isset($name)
    {
        if (isset($this->$name)) {
            return true;
        }
        return false;
    }
}