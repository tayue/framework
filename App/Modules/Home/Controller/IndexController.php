<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/8
 * Time: 15:53
 */

namespace App\Modules\Home\Controller;

use Framework\Core\Controller;
use App\Service\UserService;
use Framework\SwServer\Task\TaskManager;
use Framework\Tool\PluginManager;
use Framework\SwServer\ServerManager;
use Framework\SwServer\Process\ProcessManager;
use Framework\SwServer\Coroutine\CoroutineManager;
use Swoole\Coroutine as co;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;

class IndexController extends Controller
{
    public function indexAction()
    {
        var_dump($_POST);
        $this->assign('name', 'dsssdds');
        $this->display('index.html');

    }

    public function indexsAction()
    {
        echo "dddd";

        go(function () {
            //从池子中获取一个实例
            try {
                $pool = MysqlPoolManager::getInstance();
                $mysql = $pool->get();
                var_dump($mysql);
                if ($mysql) {
                    $result = $mysql->query("select * from user", 2);
                   // print_r($result);
                    // \Swoole\Coroutine::sleep(10); //sleep 10秒,模拟耗时操作
                    MysqlPoolManager::getInstance()->put($mysql);
                }
                echo "Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength() . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });

        go(function () {
            //从池子中获取一个实例
            try {
                $pool = RedisPoolManager::getInstance();
                $redis = $pool->get();
                var_dump($redis);
                if ($redis) {
                    $result = $redis->set('name','tayue');
                    $result1 = $redis->get('name');
                    var_dump($result,$result1);
                    // \Swoole\Coroutine::sleep(10); //sleep 10秒,模拟耗时操作
                    RedisPoolManager::getInstance()->put($redis);
                }
                echo "Current Use Redis Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });


        // $a=new MysqlPoolManager(array());
        // var_dump($a);
//       $cid= CoroutineManager::getInstance()->getCoroutineId();
//
        //   $cid1 = CoroutineManager::getInstance()->getCoroutineId();
//        var_dump($cid,$cid1);
        // 当前协程id
//        $cid1 = $cid2 = $ss = '';
//        $cid = CoroutineManager::id();
//        CoroutineManager::create(function () use (&$ss, &$cid, &$cid1, &$cid2) {
//            $cid1 = CoroutineManager::id();
//            echo "$cid=>$cid1\r\n";
//            CoroutineManager::create(function () use (&$ss, &$cid, &$cid1, &$cid2) {
//
//                $cid2 = CoroutineManager::id();
//                $ss = "$cid=>$cid1=>$cid2\r\n";
//                echo $ss . "\r\n";
//            });
//        });
//
//        var_dump($cid, $cid1, $cid2, $ss);
//        // 当前运行上下文ID, 协程环境中，顶层协程ID; 任务中，当前任务taskid; 自定义进程中，当前进程ID(pid)
//        $tid = CoroutineManager::tid();
//        //echo "{$cid1}##\r\n";
//        echo "tid:{$tid}\r\n";

//        $res=CoroutineManager::getIdMap();
//        var_dump($res);

//        $pid=$_GET['pid'];
//        $pa=ProcessManager::getInstance()->getProcessByPid($pid);
//        ProcessManager::getInstance()->writeByProcessName('CronRunner','hello CronRunner');
//
//        var_dump($pa);
//        PluginManager::getInstance()->registerFuncHook('ProcessAsyncTaskFunc',function ($a,$b){
//            return $a+$b;
//        });
//
//        PluginManager::getInstance()->triggerHook('ProcessAsyncTask',9,4);
//        echo $a;
//       new \App\Modules\Home\Controller\sss();
//
//         var_dump(ServerManager::$app);
        // $this->echo2br("App\\Modules\\Home\\Controller\\IndexController\\indexsAction\r\n");
    }

    public function taskAction()
    {

        // $res=PluginManager::getInstance()->getListeners();
        //print_r($res);
        $time = date("Y-m-d H:i:s");

        // $this->echo2br("asyncTaskId:{$taskId} Finished!\r\n");
        $a = 111;
        $b = 2;
        $c = 3;
        //$taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        // $taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        $taskId1 = TaskManager::coTask(["Server/Task/TestTask", "asyncTaskTest"], 2, $a, $b, $c);
        var_dump($taskId1);
        //TaskManager::processAsyncTask(["Server/Task/TestTask","asyncTaskTest"],$a,$b,$c);
        // $taskId=TaskManager::syncTask(["Server/Task/TestTask","syncTaskTest"],[$time],13);
        $this->echo2br("syncTaskId:{$taskId1} Finished!\r\n");
    }

    public function ddAction()
    {
        echo "ddddd";
    }

    public function init()
    {
        $this->echo2br("init\r\n");
    }

    public function __beforeAction()
    {
        $this->echo2br("__beforeAction\r\n");
    }

    public function __afterAction()
    {
        $this->echo2br("__afterAction\r\n");
    }


    protected function echo2br($str)
    {
        echo nl2br($str);
    }
}