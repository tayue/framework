<?php


namespace Framework\SwServer\Crontab;

use Cron\CronExpression;

use Framework\SwServer\ServerManager;

class Crontab
{
    use \Framework\Traits\SingletonTrait;

    private $tasks = [];

    /*
     * 同名任务会被覆盖
     */
    function addTask(string $cronTaskClass,String $method,$params): Crontab
    {
        try{
            $ref = new \ReflectionClass($cronTaskClass);
            if($ref->isSubclassOf(AbstractCronTask::class)){
                $rule = $cronTaskClass::getRule();
                if(CronExpression::isValidExpression($rule)){
                    $this->tasks[$cronTaskClass::getTaskName()] = [$cronTaskClass,$method,$params];
                }else{
                    throw new \InvalidArgumentException("the cron expression {$rule} is invalid");
                }
                return $this;
            }else{
                throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
            }
        }catch (\Throwable $throwable){
            throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
        }
    }

    /*
     * 请用户不要私自调用
     */
    function __run()
    {
        if (!empty($this->tasks)) {
            $server = ServerManager::getSwooleServer();
            $name = "server";
            $runner = new CronRunner("{$name}.Crontab", $this->tasks);
            $server->addProcess($runner->getProcess());
         }
    }

    public function getTasks(){
        return $this->tasks;
    }
}