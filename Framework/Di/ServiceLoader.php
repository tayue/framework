<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/14
 * Time: 14:31
 */

namespace Framework\Di;

use Framework\Base\Components;
use Framework\Framework;

class ServiceLoader extends Components
{
    public static $instance=null;

    private $_services = [];

    private $_definitions = [];

    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        return parent::__get($name);
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the named service is loaded.
     * @param string $name the property name or the event name
     * @return bool whether the property value is null
     */
    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        }

        return parent::__isset($name);
    }


    public function has($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_services[$id]) : isset($this->_definitions[$id]);
    }

    public function setServices($services)
    {
        foreach ($services as $id => $service) {
            $this->set($id, $service);
        }
    }

    public function get($id, $throwException = true)
    {
        if (isset($this->_services[$id])) {
            return $this->_services[$id];
        }
        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->_services[$id] = $definition;
            }
            return $this->_services[$id] = Framework::createObject($definition);
        } elseif ($throwException) {
            throw new Exception("Unknown service ID: $id");
        }

        return null;
    }

    public function set($id, $definition)
    {
        unset($this->_services[$id]);
        if ($definition === null) {
            unset($this->_definitions[$id]);
            return;
        }

        if (is_object($definition) || is_callable($definition, true)) {
            // an object, a class name, or a PHP callable
            $this->_definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->_definitions[$id] = $definition;
            } else {
                throw new Exception("The configuration for the \"$id\" service must contain a \"class\" element.");
            }
        } else {
            throw new Exception("Unexpected configuration type for the \"$id\" service: " . gettype($definition));
        }
        Framework::registerObject($id,$definition);
    }

    public function getDefinitions()
    {
        return $this->_definitions;
    }

    public function getServices()
    {
        return $this->_services;
    }

    public static function getInstance($config){
        if(!(self::$instance instanceof self)){
            self::$instance=new self($config);
        }
        return self::$instance;
    }
}