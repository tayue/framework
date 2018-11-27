<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/26
 * Time: 14:14
 */

namespace Framework\SwServer\Task;
use  Framework\SwServer\Task\Interfaces\TaskDeliveryInterface;
use Framework\SwServer\ServerManager;
class TaskDelivery implements TaskDeliveryInterface
{

    public static function asyncTask($callback, $params)
    {
       if(!ServerManager::isWorkerProcess()){
            throw new \Exception('Please deliver task by worker process!');
        }
        if(!ServerManager::isWorkerProcess() && ServerManager::isCoContext()){
            throw new \Exception('Please deliver task by http!');
        }
        $callback=array_filter($callback);
        if(!is_array($callback)){
               return false;
        }
        if(count($callback)!=2){
            return false;
        }
        $callback[0] = str_replace('/', '\\', trim($callback[0],'/'));
        list($class,$action)=$callback;
        $isExists=class_exists($class);
        if(!$isExists){
            throw new \Exception('no class exists!');
        }
        $methodExists=method_exists($class,$action);
        if(!$methodExists){
            throw new \Exception('no exists class method!');
        }
        $task_id = ServerManager::getSwooleServer()->task(\Swoole\Serialize::pack([$callback, $params]));
        unset($callable, $params, $fd);
        return $task_id;




    }
}