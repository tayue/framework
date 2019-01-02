<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 9:30
 */

namespace App\WebSocket\User;

use Framework\SwServer\Coroutine\CoroutineModel;
use Framework\SwServer\WebSocket\WST;
use Framework\SwServer\Base\BaseServerEvent;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Task\TaskManager;

class CheckService extends BaseServerEvent
{
    public function test($params)
    {

        $user = CoroutineModel::getInstance('App/WebSocket/Model/User', []);

        var_dump(count(WST::getInstance()->getApp()));
        var_dump(WST::getInstance()->getApp()->coroutine_id);
       // var_dump(CoroutineModel::$_model_instances);
        echo "websocket message \r\n";
        var_dump("coroutine_id:" . WST::getInstance()->getApp()->coroutine_id . ",fd:" . WST::getInstance()->getApp()->fd);


        return ['test'];
    }

    public function testPool()
    {
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
                echo "[" . date('Y-m-d H:i:s') . "] Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength() . ",currentNum:" . MysqlPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });
    }

    public function testTask()
    {
        $a = 111;
        $b = 2;
        $c = 3;
        $taskId1 = TaskManager::coTask(["Server/Task/TestTask", "asyncTaskTest"], 2, $a, $b, $c);
        var_dump($taskId1);
    }

}