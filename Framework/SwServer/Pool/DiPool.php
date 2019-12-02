<?php
/**
 * 容器对象池
 */

namespace Framework\SwServer\Pool;
use Framework\Traits\ComponentTrait;
use Framework\Traits\ContainerTrait;
use Framework\Traits\ServiceTrait;

class DiPool
{
    private static $instance;
    private function __construct($args = [])
    {
        $this->init($args);
    }
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    static function getInstance($args = [])
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($args);
        }
        return self::$instance;
    }

    public function init($args = [])
    {
        $this->initComponents();
        $this->initServices();
    }

    public function get($name)
    {
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
    use ComponentTrait, ServiceTrait, ContainerTrait;
}