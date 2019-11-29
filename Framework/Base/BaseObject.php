<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:30
 */

namespace Framework\Base;


use Framework\Framework;

abstract class BaseObject implements Objects
{

    public function __construct($config = [])
    {
        if (!empty($config)) {
            Framework::configure($this, $config);
        }
        $this->init();
    }

    public static function className()
    {
        return get_called_class();
    }

    public function init(){

    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new Exception('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __isset($name)
    {
        // TODO: Implement __isset() method.
    }
}