<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/26
 * Time: 13:51
 */

namespace Framework\SwServer\Task;
use Framework\SwServer\Task\Interfaces\TaskManagerInterface;
use Framework\SwServer\Task\TaskDelivery;

class TaskManager implements TaskManagerInterface
{

    public static function asyncTask($callback,$params)
    {
        $taskId=TaskDelivery::asyncTask($callback,$params);
        return $taskId;
    }

    public function coTask($callback,$params){

    }

    public static function syncTask($callback, $params, $timeout)
    {
        $taskId=TaskDelivery::syncTask($callback, $params, $timeout);
        return $taskId;
    }
}