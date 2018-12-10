<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/7
 * Time: 16:58
 */

namespace App\Crontab;


use Framework\SwServer\Crontab\AbstractCronTask;

class TaskOne extends AbstractCronTask
{
    public static $params=[];

    public static function getRule(): string
    {
        // TODO: Implement getRule() method.
        // 定时周期 （每分）
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        // 定时任务名称
        return __CLASS__;
    }

    public static function setParams($argvParams){
        return self::$params=$argvParams;
    }

    public static function run($argvParams)
    {
         var_dump($argvParams);
         $time=date("Y-m-d H:i:s");
         // 定时任务处理逻辑
        echo '['.$time.'] run once per minutes '."\r\n";
    }
}