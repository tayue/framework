<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 15:31
 */

namespace Framework\SwServer\Coroutine;


class CoroutineModel
{
    /**
     * $_instance 工厂模式的单实例
     * @var array
     */
    public static $_model_instances = [];

    /**
     * getInstance 获取model的单例
     * @param   string $class 类命名空间
     * @return  object
     */
    public static function getInstance(string $class , ...$args)
    {
        if(!$class){
            return null;
        }
        $cid = CoroutineManager::getInstance()->getCoroutineId();
        $class = str_replace('/', '\\', $class);
        $class = trim($class, '\\');
        if (isset(static::$_model_instances[$cid][$class]) && is_object(static::$_model_instances[$cid][$class])) {
            return static::$_model_instances[$cid][$class];
        }
        static::$_model_instances[$cid][$class] = new $class(...$args);
        return static::$_model_instances[$cid][$class];
    }

    /**
     * removeInstance 删除某个协程下的所有创建的model实例
     * @return boolean
     */
    public static function removeInstance($cid)
    {
        if (isset(static::$_model_instances[$cid])) {
            unset(static::$_model_instances[$cid]);
        }
        return true;
    }
}