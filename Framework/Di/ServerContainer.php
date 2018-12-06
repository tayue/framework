<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/28
 * Time: 16:10
 */

namespace Framework\Di;


class ServerContainer
{
    use \Framework\Traits\SingletonTrait;
    private $container = [];
    private $allowKeys = [
        'ProcessAsyncTask',
        'ProcessAsyncTaskFunc',
        'CustomerError'
    ];

    private function __construct(){}

    function setAllowKeys(array $allowKeys = [])
    {
        if ($allowKeys && is_array($allowKeys)) {
            $this->allowKeys = array_merge($this->allowKeys, $allowKeys);
        }
    }

    function getAllowKeys(array $allowKeys = [])
    {
        if($allowKeys){
            $this->allowKeys=array_merge($this->allowKeys,$allowKeys);
        }
        return $this->allowKeys;

    }

    function set($key, $item)
    {
        if (is_array($this->allowKeys) && !in_array($key, $this->allowKeys)) {
            return false;
        }
        if (is_callable($item)) { //回调方法
            $this->container[$key] = $item;
        } else if (is_object($item)) { //类对象
            $this->container[$key] = $item;
        }
        return $this;
    }

    function delete($key)
    {
        if (isset($this->container[$key])) {
            unset($this->container[$key]);
        }
        return $this;
    }

    function get($key)
    {
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            return false;
        }
    }

    function all(): array
    {
        return $this->container;
    }
}