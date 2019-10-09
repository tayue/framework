<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/8
 * Time: 15:53
 */

namespace App\Modules\Home\Controller;

use App\Service\Util;
use App\Listener\SendSmsListener;
use App\Listener\SendEmailsListener;
use Framework\Core\Controller;
use App\Service\User;
use App\Service\Crypt;
use Framework\SwServer\Task\TaskManager;
use Framework\Tool\PluginManager;
use Framework\SwServer\ServerManager;
use Framework\SwServer\Process\ProcessManager;
use Framework\SwServer\Coroutine\CoroutineManager;
use Swoole\Coroutine as co;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;
use Framework\SwServer\Inotify\Daemon;
use Framework\SwServer\ServerController;
use Framework\SwServer\Event\Event;
use Framework\SwServer\Event\EventManager;
use Framework\SwServer\Pool\DiPool;
class IndexController extends ServerController
{
    public $userService;
    public $util;
    private $event;


    public function __construct(User $userService,Util $util)
    {  //依赖注入
        parent::__construct();
        $this->userService=$userService;
        $this->util=$util;

    }

    public function indexAction(Crypt $crypt,Event $e,SendSmsListener $smlistener,SendEmailsListener $semaillistener)
    {
//        $em=ServerManager::getApp()->eventmanager;
//        $services=DiPool::getInstance()->getServices();
//        $comments=DiPool::getInstance()->getComponents();
//        $e->setName("createorder");
//        $em->addListener($smlistener,["createorder"=>1]);
//        $em->addListener($semaillistener,["createorder"=>2]);
//        $em->trigger("createorder",null,['test','test1']);
//        $context = new Co\Context(); //swoole 协程上下文管理器注册上下文环境后协程执行完成后自动回收
//        $context['crypt'] = $crypt;
//
//
//       // $userService->display();
//
//
//        $crypt->display();
//
//        $this->userService->display();
//       // $userData1 = ServerManager::getApp()->userService->findUser();
//       // $userData2 = ServerManager::getApp()->userService->findUser();
//        $userData1=$this->userService->findUser();
//        $userData2=$this->userService->findUser();

        // print_r(ServerManager::getApp('cid_4'));

//        print_r($userData1);
//        print_r($userData2);
        $this->assign('name', 'Swoole Http Server !!!');
        $this->display('index.html');

    }

    public function packAction()
    {
        $len = 10;
        $data = ['code' => 200, 'data' => "hello world !!!"];
        $body = self::encode($data, 1);
        $header = ['length' => 'N', 'name' => 'a30'];
        $bin_header_data = '';
        foreach ($header as $key => $value) {
            if (isset($header[$key])) {
                // 计算包体长度
                if ($key == 'length') {
                    $bin_header_data .= pack($value, strlen($body));

                } else {
                    // 其他的包头
                    $bin_header_data .= pack($value, $header[$key]);
                }
            }
        }

        $resData = $bin_header_data . $body;
        $unpack_length_type = '';
        if ($header) {
            foreach ($header as $key => $value) {
                $unpack_length_type .= ($value . $key) . '/';
            }
        }
        $unpack_length_type = trim($unpack_length_type, '/');
        $header = unpack($unpack_length_type, mb_strcut($resData, 0, 45, 'UTF-8'));
        $pack_body = mb_strcut($resData, 45, null, 'UTF-8');

        var_dump($header, $pack_body);


    }

    /**
     * encode 数据序列化
     * @param   mixed $data
     * @param   int $serialize_type
     * @return  string
     */

    public static function encode($data, $serialize_type = 1)
    {

        switch ($serialize_type) {
            // json
            case 1:
                return json_encode($data);
            // serialize
            case 2:
                return serialize($data);
            case 3;
            default:
                // swoole
                return \Swoole\Serialize::pack($data);
        }
    }


    public
    function indexsAction()
    {
//        go(function () {
//            //从池子中获取一个实例
//            try {
//                $resourceData = MysqlPoolManager::getInstance()->get(5);
//                if ($resourceData) {
//                    $result = $resourceData['resource']->query("select * from user", 2);
//                    print_r($result);
//                    //\Swoole\Coroutine::sleep(4); //sleep 10秒,模拟耗时操作
//                    MysqlPoolManager::getInstance()->put($resourceData);
//                }
//                echo "[" . date('Y-m-d H:i:s') . "] Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength() . ",currentNum:" . MysqlPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//
//            } catch (\Exception $e) {
//                echo $e->getMessage();
//            }
//        });
//
//
//        go(function () {
//            //从池子中获取一个实例
//            try {
//                $resourceData = RedisPoolManager::getInstance()->get(5);
//                if ($resourceData) {
//                    $result = $resourceData['resource']->set('name', 'tayue');
//                    $result1 = $resourceData['resource']->get('name');
//                    print_r($result1);
//                    //\Swoole\Coroutine::sleep(4);
//                    RedisPoolManager::getInstance()->put($resourceData);
//                }
//                echo "[" . date('Y-m-d H:i:s') . "] Current Use Redis Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//
//            } catch (\Exception $e) {
//                echo $e->getMessage();
//            }
//        });

        $services=DiPool::getInstance()->getServices();
        $comments=DiPool::getInstance()->getComponents();
        $this->util->display();
        echo ServerManager::getApp()->userService->display();
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

    public
    function taskAction()
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
        TaskManager::processAsyncTask(["Server/Task/TestTask","asyncTaskTest"],$a,$b,$c);
        // $taskId=TaskManager::syncTask(["Server/Task/TestTask","syncTaskTest"],[$time],13);
        $this->echo2br("syncTaskId:{$taskId1} Finished!\r\n");
    }

    public
    function dateAction()
    {
        echo date("Y-m-d H:i:s");
    }

    public
    function ddAction()
    {
//        $s = new \SphinxClient;
//        $s->setServer("localhost", 9312);
//        $s->setMatchMode(SPH_MATCH_ANY);
//        $s->setMaxQueryTime(3);
//
//        $result = $s->query("test");

        $sphinx = new \SphinxClient;
        $sphinx->setServer("localhost", 9312);

        $sphinx->SetArrayResult(true);
        $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
        $sphinx->SetSelect("*");
        $sphinx->ResetFilters();
        //$sphinx->SetFilter('product_id', array(14001949));
        $query = " @amazon_item_name 'Universal'"; //@amazon_item_name  备注（amazon_item_name） 是索引列的字段
        $result = $sphinx->query($query, "blog");    //星号为所有索引源
        var_dump($result);
        echo '<pre>';
        print_r($result);
        $count = $result['total'];        //查到的结果条数
        $time = $result['time'];            //耗时
        $arr = $result['matches'];        //结果集
        $id = '';
        for ($i = 0; $i < $count; $i++) {
            $id .= $arr[$i]['id'] . ',';
        }
        $id = substr($id, 0, -1);            //结果集的id字符串


        echo '<pre>';
        print_r($arr);
        echo $id;
    }

    public
    function init()
    {
        $this->echo2br("init\r\n");
    }

    public
    function __beforeAction()
    {
        $this->echo2br("__beforeAction\r\n");
    }

    public
    function __afterAction()
    {
        $this->echo2br("__afterAction\r\n");
    }


    protected
    function echo2br($str)
    {
        echo nl2br($str);
    }
}
