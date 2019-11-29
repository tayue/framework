<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:20
 */

namespace Framework;

class Framework
{
    public static $container;
    public static $app;
    public static $service;
    public static $debug = true;

    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

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

    public static function getApp()
    {
        return self::$app;
    }

    public static function getModule()
    {
        return self::$app->current_module;
    }

    public static function getController()
    {
        return self::$app->current_controller;
    }

    public static function getAction()
    {
        return self::$app->current_action;
    }

    public static function getProjectType()
    {
        return self::$app->project_type;
    }

    public static function getService()
    {
        return self::$service;
    }

}