<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/26
 * Time: 12:03
 */
namespace Server\Task;
use Framework\SwServer\Task\TaskManager;
class TestTask
{
    public function test(){
        $time=date("Y-m-d H:i:s");
        $task_id=TaskManager::asyncTask(['Server/Task/TestTask', 'asyncTaskTest'], ['swoole '.$time]);
        return $task_id;
    }

    public function asyncTaskTest($params){
        var_dump('asyncTaskTest');
    }




}