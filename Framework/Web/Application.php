<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:18
 */

namespace Framework\Web;
use Framework\Framework;
use Framework\Core\Db;
use Framework\Core\View;
use Framework\Core\Route;
use Framework\Tool\Log;
class Application extends \Framework\Base\Application
{
   public function run($config){

       include_once VENDOR_PATH.'/autoload.php';
       Db::setConfig($config['components']['db']['config']);
       Route::parseRouteUrl();
       Log::getInstance()->setConfig(['log_dir'=>$config['log']['log_dir']]);
       Log::getInstance()->put("hello world",'error');

   }


}