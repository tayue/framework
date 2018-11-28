<?php

namespace Server\Task;
use Framework\Core\Exception;
use Framework\SwServer\Task\TaskManager;
class TestTask
{
    public function test(){
        $time=date("Y-m-d H:i:s");
        $task_id=TaskManager::asyncTask(['Server/Task/TestTask', 'asyncTaskTest'], ['swoole '.$time]);
        return $task_id;
    }

    public function asyncTaskTest($params){
        for($i=1;$i<=10;$i++){
            echo "i:{$i}\r\n";
            sleep(1);
        }
    }

    public function syncTaskTest($params){
       // try{
        for($i=1;$i<=12;$i++){
            echo "syncTaskTest i:{$i}\r\n";
            sleep(1);
        }
       // exit(0);
//        }catch (\Throwable $e){
//            echo $e->getMessage();
//        }

    }




}