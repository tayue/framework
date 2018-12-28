<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:30
 */

namespace Framework\SwServer\Base;


abstract class BaseObjects implements Objects
{

    public static function className()
    {
        return get_called_class();
    }

    public function init()
    {

    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
    }


    public function __isset($name)
    {
        if (isset($this->$name)) {
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return get_called_class();
    }
}