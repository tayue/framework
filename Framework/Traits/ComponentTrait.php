<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

use Framework\SwServer\ServerManager;

trait ComponentTrait
{
    private $_components = [];

    public function setComponents($components)
    {
        foreach ($components as $id => $component) {
            $this->createComponentObject($id, $component);
        }
    }

    /**
     * coreComponents 定义核心组件
     * @return   array
     */
    public function coreComponents()
    {
        return [];
    }

    /**
     * creatObject 创建组件对象
     * @param    string $com_alias_name 组件别名
     * @param    array $defination 组件定义类
     * @return   array
     */

    public function createComponentObject(string $com_alias_name = null, array $defination = [])
    {
        // 动态创建公用组件
        if (!isset($this->_components[$com_alias_name])) {
            if (isset($defination['class'])) {
                $class = $defination['class'];
                if (!isset($this->_singletons[$class])) {
                    $params = [];
                    if (isset($defination['constructor'])) {
                        $params = $defination['constructor'];
                        unset($defination['constructor']);
                    }
                    $this->registerObject($com_alias_name, $defination, $params);
                    $this->_components[$com_alias_name] = $class;
                    return $this->_singletons[$class];
                } else {
                    return $this->_singletons[$class];
                }
            } else {
                throw new \Exception("component:" . $com_alias_name . 'must be set class', 1);
            }
        } else {
            return $this->_singletons[$this->_components[$com_alias_name]];
        }
        return false;
    }


    /**
     * clearComponent
     * @param    string|array $component_alias_name
     * @return   boolean
     */
    public function clearComponent($com_alias_name = null)
    {
        if (!is_null($com_alias_name) && is_string($com_alias_name)) {
            $com_alias_name = (array)$com_alias_name;
        } else if (is_array($com_alias_name)) {
            $com_alias_name = array_unique($com_alias_name);
        } else {
            return false;
        }
        foreach ($com_alias_name as $alias_name) {
            unset($this->_singletons[$this->_components[$alias_name]]);
            unset($this->_components[$alias_name]);
        }
        return true;
    }


    public function initComponents()
    {
        // 配置文件初始化创建公用对象
        $coreComponents = $this->coreComponents();
        $components = array_merge($coreComponents, ServerManager::$config['components']);
        foreach ($components as $com_name => $component) {
            // 存在直接跳过
            if (isset($this->_components[$com_name])) {
                continue;
            }
            if (isset($component['class']) && $component['class'] != '') {
                $params = [];
                if (isset($component['constructor'])) {
                    $params = $component['constructor'];
                    unset($component['constructor']);
                }
                $defination = $component;
                $this->registerObject($com_name, $defination, $params);
                $this->_components[$com_name] = $component['class'];
            }
        }
        return $this->_singletons;
    }


    public function getComponents()
    {
        return $this->_components;
    }

    public function getComponent($alias_name)
    {
        if (isset($this->_components[$alias_name])) {
            return $this->_singletons[$this->_components[$alias_name]];
        } else if (in_array($alias_name, array_keys(ServerManager::$config['components']))) {
            return $this->createComponentObject($alias_name, ServerManager::$config['components'][$alias_name]);
        }
        return false;
    }


}