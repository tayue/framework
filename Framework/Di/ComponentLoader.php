<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 17:18
 */

namespace Framework\Di;


use Framework\Base\Components;
use Framework\Framework;

class ComponentLoader extends Components
{

    private $_components = [];

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
     * This method overrides the parent implementation by checking if the named component is loaded.
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
        return $checkInstance ? isset($this->_components[$id]) : isset($this->_definitions[$id]);
    }

    public function setComponents($components)
    {
        foreach ($components as $id => $component) {
            $this->set($id, $component);
        }
    }

    public function get($id, $throwException = true)
    {
        if (isset($this->_components[$id])) {
            return $this->_components[$id];
        }
        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->_components[$id] = $definition;
            }
            return $this->_components[$id] = Framework::createObject($definition);
        } elseif ($throwException) {
            throw new Exception("Unknown component ID: $id");
        }

        return null;
    }

    public function set($id, $definition)
    {
        unset($this->_components[$id]);
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
                throw new Exception("The configuration for the \"$id\" component must contain a \"class\" element.");
            }
        } else {
            throw new Exception("Unexpected configuration type for the \"$id\" component: " . gettype($definition));
        }
        Framework::registerObject($id,$definition);
    }

    public function getDefinitions()
    {
        return $this->_definitions;
    }

    public function getComponents()
    {
        return $this->_components;
    }


}