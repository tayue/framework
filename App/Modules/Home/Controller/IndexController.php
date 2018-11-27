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
use Framework\Framework;
class IndexController extends Controller
{
   public function indexAction(){
         var_dump($_GET);
         $this->assign('name','dsssdds');
         $this->display('index.html');

   }

    public function indexsAction(){

        $this->echo2br("App\\Modules\\Home\\Controller\\IndexController\\indexsAction\r\n");
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