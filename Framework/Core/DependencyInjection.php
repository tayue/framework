<?php
/**
 * php利用反射特性模拟java类依赖注入
 */

namespace Framework\Core;

use Framework\Traits\ContainerTrait;
use Framework\Traits\SingletonTrait;
use Framework\SwServer\ServerManager;

class DependencyInjection
{

    public function run($instance, $method, $parameters = [])
    {
        $parameters = $this->resolveClassMethodDependencies($instance, $method, $parameters);
        if (!\method_exists($instance = new $instance(), $method)) {
            throw new \Exception("Controller Create Faild !");
        }
        return \call_user_func_array([$instance, $method], $parameters);
    }

    public function resolveClassMethodDependencies($instance, $method, $parameters)
    {
        if (!\method_exists($instance, $method)) {
            return $parameters;
        }
        return $this->resolveMethodDependencies(
            new \ReflectionMethod($instance, $method), $parameters
        );


    }

    public function resolveMethodDependencies(\ReflectionFunctionAbstract $reflector, array $parameters)
    {
        $originalParameters = $parameters;
        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = self::transformDependency(
                $parameter, $parameters, $originalParameters
            );
            if (!is_null($instance)) {
                if ($originalParameters) {
                    $this->spliceIntoParameters($parameters, $key, $instance);
                } else {
                    $parameters[] = $instance;
                }

            }
        }

        return $parameters;
    }

    public function transformDependency(\ReflectionParameter $parameter, $parameters, $originalParameters)
    {
        $class = $parameter->getClass();
        if ($class) {
            $type['class'] = $class->name;
            return ServerManager::getApp()->registerObject($type['class'], $type);
        }
    }

    public function spliceIntoParameters(array &$parameters, $offset, $value)
    {
        array_splice(
            $parameters, $offset, 0, [$value]
        );
    }

    use SingletonTrait;
}
