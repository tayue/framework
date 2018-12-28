<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

trait ContainerTrait
{
    public static $container;

    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new Exception('Object configuration must be an array containing a "class" element.');
        }

        throw new Exception('Unsupported configuration type: ' . gettype($type));
    }

    public static function registerObject($id, $definition = [], array $params = [])
    {
        if ($id && is_array($definition) && isset($definition['class'])) {
            $class = $definition['class'];
            unset($definition['class']);
            return static::$container->setSingleton($class, $definition, $params);
        }
        throw new Exception('No registerObject');
    }
}