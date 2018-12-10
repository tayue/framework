<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:13
 */

namespace Framework\SwServer\Crontab;

use Cron\CronExpression;
use Framework\SwServer\Process\AbstratProcess;
use Swoole\Process;
use Framework\SwServer\Timer\TimerCallbak;
use Framework\SwServer\Task\TaskManager;

class CronRunner extends AbstratProcess
{
    protected $tasks;

    public function run(Process $process)
    {
        $this->tasks = $this->getArgs();
        $this->cronProcess();
        TimerCallbak::loop(10 * 1000, function () {
            $this->cronProcess();
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }


    private function cronProcess()
    {
        foreach ($this->tasks as $task) {
            list($class, $method, $params) = $task;
            $cronRule = $class::getRule();
            $nextRunTime = CronExpression::factory($cronRule)->getNextRunDate();
            $distanceTime = $nextRunTime->getTimestamp() - time();
            if ($distanceTime < 30) {
                TimerCallbak::delay($distanceTime * 1000, function () use ($class,$method,$params) {
                    TaskManager::processAsyncTask([$class, $method],$params);
                });
            }
        }
    }

    public function onReceive($str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}