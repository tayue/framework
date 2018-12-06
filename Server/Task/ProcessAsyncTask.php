<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/29
 * Time: 9:21
 */

namespace Server\Task;


class ProcessAsyncTask
{
    public function onPipeMessage($processObjectData, $processObjectParams)
    {
        list($classNameSpacePath, $method) = $processObjectData;
        $classObject = new $classNameSpacePath();
        $controllerInstance = new \ReflectionClass($classNameSpacePath);
        if ($controllerInstance->hasMethod($method)) {
            $classMethod = new \ReflectionMethod($classNameSpacePath, $method);
            if ($classMethod->isPublic() && !$classMethod->isStatic()) {
                try {
                    $classObject->$method(...$processObjectParams);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(), 2);
                } catch (\Throwable $t) {

                    throw new \Exception($t->getMessage(), 1);
                }
            } else {
                throw new \Exception('class method ' . $method . ' is static or private, protected property, can not be object call!', 1);
            }
        }
    }
}