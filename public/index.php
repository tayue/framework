<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:19
 */

use Framework\Web\Application;
error_reporting(E_ALL);
define("BASE_DIR", dirname(__DIR__));
spl_autoload_register('myAutoLoad', true, false);
function myAutoLoad($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $classPath = $className . '.php';
    $fileClassPath = BASE_DIR . DIRECTORY_SEPARATOR . $classPath;
    if (file_exists($fileClassPath)) {
        include_once $fileClassPath;
    }
}

include_once '../App/Config/defines.php';
$config = include_once '../App/Config/config.php';
$service = include_once '../App/Config/service.php';
include_once VENDOR_PATH . '/autoload.php';

(new Application($config))->run($config);

