<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:30
 */

namespace Framework\SwServer\Base;


abstract class BaseObject implements Objects
{

    public static function className()
    {
        return get_called_class();
    }


    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (isset($this->$name)) {
            return $this->$name;
        } else if (method_exists($this, $getter)) {
            return $this->$getter();
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