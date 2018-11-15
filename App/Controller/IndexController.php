<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/8
 * Time: 15:53
 */
namespace App\Controller;
class IndexController
{
   public function indexAction(){
       print_r($_GET);
     //  echo "App\\Controller\\IndexController\\indexAction";
   }

   public function __call($name, $arguments)
   {
       // TODO: Implement __call() method.
       var_dump($name,$arguments);
   }
}