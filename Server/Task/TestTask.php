<?php

namespace Server\Task;
use Framework\Core\Exception;
use Framework\SwServer\Task\TaskManager;
use Framework\Framework;
use Swoole\Coroutine as co;
class TestTask
{
    public $name="TestTask";
    public function test(){
        $time=date("Y-m-d H:i:s");
        $task_id=TaskManager::asyncTask(['Server/Task/TestTask', 'asyncTaskTest'], ['swoole '.$time]);
        return $task_id;
    }

    public function asyncTaskTest($a,$b,$c){

            $cd=$a+$b+$c;
            $time=date("Y-m-d H:i:s ");
            echo $time.$cd."\r\n";




    }

    public function syncTaskTest($params)
    {
        try {
//            for ($i = 1; $i <= 12; $i++) {
//                echo "syncTaskTest i:{$i}\r\n";
//                sleep(1);
//            }
            $this->tests($this->name);
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }



    }

    public function tests($name){
        $userService=Framework::getService()->user;
        $userService->display();
        echo "function test ".$name;
    }




}