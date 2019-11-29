<?php
/**
 * php利用反射特性模拟java类依赖注入
 */

namespace Framework\Core;

use Framework\SwServer\Pool\DiPool;

class DependencyInjection
{
    public static function make($className, $methodName, $params = [])
    {
        // 获取类的实例
        $instance = self::getInstance($className);
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::resolveClassMethodDependencies($className, $methodName);
        return $instance->{$methodName}(...array_merge($paramArr, $params));
    }

    public static function getInstance($className)
    {
        $paramArr = self::resolveClassMethodDependencies($className);
        return DiPool::getInstance()->registerObject($className, ['class' => $className], $paramArr);
    }

    public static function resolveClassMethodDependencies($className, $method = '__construct')
    {
        $parameters = []; // 记录参数，和参数类型
        if (!\method_exists($className, $method)) {
            return $parameters;
        }
        // 获得构造函数
        $reflector = new \ReflectionMethod($className, $method);
        if (count($reflector->getParameters()) <= 0) {
            return $parameters;
        }
        foreach ($reflector->getParameters() as $key => $parameter) {
            $currentParamsReflectionClass = $parameter->getClass();
            if ($currentParamsReflectionClass) {
                // 获得参数类型名称
                $paramClassName = $currentParamsReflectionClass->getName();
                $paramClassParams = self::resolveClassMethodDependencies($paramClassName);
                $parameters[] = DiPool::getInstance()->registerObject($paramClassName, ['class' => $paramClassName], $paramClassParams);
            }

        }
        return $parameters;
    }


}

