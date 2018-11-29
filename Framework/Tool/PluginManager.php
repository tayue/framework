<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/28
 * Time: 16:25
 */

namespace Framework\Tool;

use Framework\Di\ServerContainer;

class PluginManager
{
    use \Framework\Traits\SingletonTrait;
    /**
     * 监听已注册的插件
     *
     * @access private
     * @var array
     */
    private $_listeners = array();

    public function registerClassHook($hook, $className, $method)
    {
        if (!$hook || !$className || !$method) {
            return false;
        }
        $className = str_replace('/', '\\', trim($className, '/'));
        $isExists = class_exists($className);
        $hookKey = $className . "->" . $method;
        if (isset($this->_listeners[$hook]) && $this->_listeners[$hook]) {
            return false;
        }
        if (!$isExists) {
            throw new \Exception('no class exists!');
        }
        $methodExists = method_exists($className, $method);
        if (!$methodExists) {
            throw new \Exception('no exists class method!');
        }
        $hookRegisterObject = new $className();
        $this->_listeners[$hook][$hookKey] = [$hookRegisterObject, $method];

    }

    public function registerFuncHook($hook, $callback)
    {
        if (!$hook || !$callback) {
            return false;
        }
        if (!is_callable($callback)) {
            return false;
        }
        if (isset($this->_listeners[$hook]) && $this->_listeners[$hook]) {
            return false;
        }
        $hookKey = "Func" . date("YmdHis") . rand(111111, 99999);
        $hookRegisterObject = '';
        $res = ServerContainer::getInstance()->set($hook, $callback);
        if (!$res) {
            unset($hookRegisterObject);
            throw new \Exception('registerFuncHook Faild!');
        }

        $this->_listeners[$hook][$hookKey] = $callback;
    }

    public function triggerHook($key, ...$params)
    {
        try {
            if (isset($this->_listeners[$key]) && $this->_listeners[$key]) {
                foreach ($this->_listeners[$key] as $key => $hookData) {
                    if ($hookData instanceof \Closure) {
                        call_user_func($hookData, ...$params);
                    } else if (is_array($hookData)) {
                        call_user_func($hookData, ...$params);
                    }
                }

            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    public function hasHook($hook)
    {
        $flag = false;
        if (isset($this->_listeners[$hook]) && $this->_listeners[$hook]) {
            $flag = true;
        }
        return $flag;
    }

    public function getListeners()
    {
        return $this->_listeners;
    }


}