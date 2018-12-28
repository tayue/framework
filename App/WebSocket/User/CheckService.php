<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 9:30
 */
namespace App\WebSocket\User;
use Framework\SwServer\WebSocket\WST;
use Framework\SwServer\Base\BaseServerEvent;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Task\TaskManager;
class CheckService extends BaseServerEvent
{
  public function test($params){
        $this->testTask();
        var_dump($params,$this->mixedParams);
        $db=WST::$app->db;
        print_r($db);
        return ['test'];
    }

    public function testPool(){
        go(function () {
            //从池子中获取一个实例
            try {
                $resourceData = MysqlPoolManager::getInstance()->get(5);
                if ($resourceData) {
                    $result = $resourceData['resource']->query("select * from user", 2);
                    print_r($result);
                    //\Swoole\Coroutine::sleep(4); //sleep 10秒,模拟耗时操作
                    MysqlPoolManager::getInstance()->put($resourceData);
                }
                echo "[".date('Y-m-d H:i:s')."] Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength().",currentNum:".MysqlPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });
    }

    public function testTask(){
        $a = 111;
        $b = 2;
        $c = 3;
        //$taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        // $taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        $taskId1 = TaskManager::coTask(["Server/Task/TestTask", "asyncTaskTest"], 2, $a, $b, $c);
        var_dump($taskId1);
    }

}