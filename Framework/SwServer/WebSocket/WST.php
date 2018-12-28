<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:20
 */

namespace Framework\SwServer\WebSocket;


class WST
{
    use \Framework\Traits\ContainerTrait;
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