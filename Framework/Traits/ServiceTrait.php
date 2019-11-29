<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

use Framework\SwServer\ServerManager;

trait ServiceTrait
{
    private $_services = [];

    public function setServices($services)
    {
        foreach ($services as $id => $service) {
            $this->createServiceObject($id, $service);
        }
    }

    /**
     * coreServices 定义核心服务
     * @return   array
     */
    public function coreServices()
    {
        return [];
    }

    /**
     * creatObject 创建服务对象
     * @param    string $com_alias_name 组件别名
     * @param    array $defination 组件定义类
     * @return   array
     */

    public function createServiceObject(string $com_alias_name = null, array $defination = [])
    {
        // 动态创建公用组件
        if (!isset($this->_services[$com_alias_name])) {
            if (isset($defination['class'])) {
                $class = $defination['class'];
                if (!isset($this->_singletons[$class])) {
                    $params = [];
                    if (isset($defination['constructor'])) {
                        $params = $defination['constructor'];
                        unset($defination['constructor']);
                    }
                    $this->registerObject($com_alias_name, $defination, $params);
                    $this->_services[$com_alias_name] = $class;
                    return $this->_singletons[$class];
                } else {
                    return $this->_singletons[$class];
                }
            } else {
                throw new \Exception("service:" . $com_alias_name . 'must be set class', 1);
            }
        } else {
            return $this->_singletons[$this->_services[$com_alias_name]];
        }
        return false;
    }


    /**
     * clearService
     * @param    string|array $service_alias_name
     * @return   boolean
     */
    public function clearService($com_alias_name = null)
    {
        if (!is_null($com_alias_name) && is_string($com_alias_name)) {
            $com_alias_name = (array)$com_alias_name;
        } else if (is_array($com_alias_name)) {
            $com_alias_name = array_unique($com_alias_name);
        } else {
            return false;
        }
        foreach ($com_alias_name as $alias_name) {
            unset($this->_singletons[$this->_services[$alias_name]]);
            unset($this->_services[$alias_name]);
        }
        return true;
    }

    public function initServices()
    {
        // 配置文件初始化创建公用对象
        $coreServices = $this->coreServices();
        $services = array_merge($coreServices, ServerManager::$config['services']);
        foreach ($services as $com_name => $service) {
            // 存在直接跳过
            if (isset($this->_services[$com_name])) {
                continue;
            }
            if (isset($service['class']) && $service['class'] != '') {
                $params = [];
                if (isset($service['constructor'])) {
                    $params = $service['constructor'];
                    unset($service['constructor']);
                }
                $defination = $service;
                $this->createServiceObject($com_name, $defination, $params);
                $this->_services[$com_name] = $service['class'];
            }
        }
        return $this->_singletons;
    }


    public function getServices()
    {
        return $this->_services;
    }

    public function getService($alias_name)
    {
        if (isset($this->_services[$alias_name])) {
            return $this->_singletons[$this->_services[$alias_name]];
        } else if (in_array($alias_name, array_keys(ServerManager::$config['services']))) {
            return $this->createServiceObject($alias_name, ServerManager::$config['services'][$alias_name]);
        }
        return false;
    }


}
