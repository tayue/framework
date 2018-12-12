<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/26
 * Time: 13:52
 */
namespace Framework\SwServer\Task\Interfaces;
interface TaskManagerInterface{
    public static function asyncTask($callback,...$params);
    public static function syncTask($callback,$timeout,...$params);
    public static function coTask($callback,$timeout,...$params);
    public static function processAsyncTask($callback,...$params);
}