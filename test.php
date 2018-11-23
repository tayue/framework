<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 16:52
 */
use Framework\SwServer\ServerManager;
$config=[
    'log'=>[
        'log_dir'=>__DIR__
    ]
];
define("BASE_DIR", __DIR__);
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
$sm=new ServerManager();
$sm->createServer($config);