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

    public static function asyncTask($callback, ...$params)
    {
        $taskId = TaskDelivery::asyncTask($callback, ...$params);
        return $taskId;
    }

    public static function coTask($callback,$timeout,...$params)
    {
        $taskId = TaskDelivery::coTask($callback, $timeout, ...$params);
        return $taskId;
    }

    public static function syncTask($callback, $timeout, ...$params)
    {
        $taskId = TaskDelivery::syncTask($callback, $timeout, ...$params);
        return $taskId;
    }

    public static function processAsyncTask($callback, ...$params)
    {
        $taskId = TaskDelivery::processAsyncTask($callback, ...$params);
        return $taskId;
    }
}