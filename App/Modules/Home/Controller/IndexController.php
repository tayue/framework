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
class IndexController extends Controller
{
   public function indexAction(){
       var_dump($_POST);
         $this->assign('name','dsssdds');
         $this->display('index.html');

   }

    public function indexsAction(){

        $pid=$_GET['pid'];
        $pa=ProcessManager::getInstance()->getProcessByPid($pid);
        ProcessManager::getInstance()->writeByProcessName('CronRunner','hello CronRunner');

        var_dump($pa);
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

    public function taskAction(){

       // $res=PluginManager::getInstance()->getListeners();
        //print_r($res);
        $time=date("Y-m-d H:i:s");

       // $this->echo2br("asyncTaskId:{$taskId} Finished!\r\n");
        $a=111;
        $b=2;
        $c=3;
        $taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        //TaskManager::processAsyncTask(["Server/Task/TestTask","asyncTaskTest"],$a,$b,$c);
       // $taskId=TaskManager::syncTask(["Server/Task/TestTask","syncTaskTest"],[$time],13);
        $this->echo2br("syncTaskId:{$taskId} Finished!\r\n");
    }

    public function ddAction(){
        echo "ddddd";
    }

   public function init(){
       $this->echo2br("init\r\n");
   }

    public function __beforeAction(){
        $this->echo2br("__beforeAction\r\n");
    }

    public function __afterAction(){
        $this->echo2br("__afterAction\r\n");
    }



    protected function echo2br($str){
       echo nl2br($str);
    }
}