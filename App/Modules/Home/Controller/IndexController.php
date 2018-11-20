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
//var_dump(Framework::$container);


       //Framework::getApp()->user->display();
     // Framework::getService()->user->display();
//var_dump(Framework::getService()->user);
     //  var_dump(Framework::$container->trait_dd);
         $this->assign('name','dsssdd');
         $this->display('index.html');


//       $listObj = (new User())->where(['sex'=>1])->order('id ASC')->all()->toArray();
//        print_r($listObj);


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