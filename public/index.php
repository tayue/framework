<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:19
 */

use Framework\Web\Application;

header("Content-type:text/html;charset=utf-8");
ini_set("display_errors", "On");
date_default_timezone_set('UTC');
error_reporting(E_ALL);
define("BASE_DIR", dirname(__DIR__));

/*************自定义命名空间加载类**************/
class Autoloader
{
    /**
     * $directory 当前的目录
     * @var [type]
     */
    private static $baseDirectory = __DIR__;

    /**
     * $prefix 自定义的根命名空间
     * @var array
     */
    private static $root_namespace = ['App', 'Framework', 'Server'];

    public static function setBaseDirectory($baseDirectory = '')
    {
        if ($baseDirectory) {
            self::$baseDirectory = $baseDirectory;
        }
    }

    /**
     * @param string $className
     * @return boolean
     */
    public static function autoload($className)
    {
        foreach (self::$root_namespace as $k => $namespace) {
            // 判断如果以\命名空间访问的格式符合
            if (0 === strpos($className, $namespace)) {
                //分隔出$this->prefixLength个字符串以后的字符返回，再以\为分隔符分隔
                $parts = explode('\\', $className);
                // 组合新的路径
                $filepath = self::$baseDirectory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
                if (is_file($filepath)) {
                    require_once $filepath;
                }
                // 匹配到符合的,结束循环
                break;
            }
        }
    }

    /**
     * 注册自动加载
     */
    public static function register($prepend = false)
    {
        if (!function_exists('__autoload')) {
            spl_autoload_register(array('Autoloader', 'autoload'), true, $prepend);
        } else {
            trigger_error('spl_autoload_register() which will bypass your __autoload() and may break your autoloading', E_USER_WARNING);
        }
    }
}

Autoloader::setBaseDirectory(BASE_DIR);
Autoloader::register();

include_once '../App/Config/defines.php';
$config = include_once '../App/Config/config.php';
$serverConfig = include_once '../App/Config/server.php';
$config = array_merge($config, $serverConfig);
include_once VENDOR_PATH . '/autoload.php';
(new Application($config))->run($config);

