<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/26
 * Time: 12:02
 */

namespace Framework\SwServer\Task\Interfaces;


interface QuickTaskInterface
{
    static function run(\swoole_server $server,int $taskId,int $fromWorkerId);
}