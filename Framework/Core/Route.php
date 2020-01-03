<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/8
 * Time: 16:02
 */

namespace Framework\Core;

use Framework\Framework;



class Route
{

    public static $routeCacheFileMap;

    //地址路由解析 有两种模式一种是pathInfo一种是query_string模式
    public static function parseRouteUrl()
    {
        try {
            $validate = true;
            $projectType = Framework::$app->project_type;
            $pattern = '/^[0-9a-zA-Z]+$/'; //验证一些参数
            $paramsValPattern = '/[^0-9a-zA-Z]/'; //验证地址参数值(除了字符以外的任意参数)
            $appNameSpace = Framework::$app->project_namespace;
            $_module = $_controller = $_action = '';
            self::filter();
            //如果使用了pathinfo的模式的话用pathinfo模式去解析url地址的参数
            if (isset($_SERVER['PATH_INFO'])) {
                if (Framework::$app->routeRule != 1) {
                    $validate = false;
                }
                $pathInfo = explode('/', trim($_SERVER['PATH_INFO'], '/'));
                $offset = 2;
                //先检查项目模块化配置
                if ($projectType == 1) { //模块化
                    if (count($pathInfo) < 3) {
                        $validate = false;
                    }
                    @list($module, $controller, $action) = $pathInfo;
                    $moduleValidate = self::validate($pattern, $module);
                    if (!$moduleValidate || !$module) {
                        $validate = false;
                    }
                    $offset = 3;
                } else { //非模块化
                    if (count($pathInfo) < 2) {
                        $validate = false;
                    }
                    list($controller, $action) = $pathInfo;
                }

                $controllerValidate = self::validate($pattern, $controller);
                $actionValidate = self::validate($pattern, $action);
                if (!$controllerValidate || !$actionValidate) {
                    $validate = false;
                }
                if (!$validate) {
                    $_module = Framework::$app->default_module;
                    $_controller = Framework::$app->default_controller;
                    $_action = Framework::$app->default_action;
                } else {
                    $pathInfoParams = array_slice($pathInfo, $offset); //参数数组
                    for ($i = 0; $i < count($pathInfoParams); $i += 2) { //过滤掉一些不正确的参数key
                        $validateKeyResult = self::validate($pattern, $pathInfoParams[$i]);
                        $validateValResult = self::validate($paramsValPattern, $pathInfoParams[$i + 1]);
                        $paramsVal = $validateValResult == true ? htmlspecialchars(addslashes(trim($pathInfoParams[$i + 1]))) : $pathInfoParams[$i + 1];
                        ($validateKeyResult == true && $pathInfoParams[$i]) && $_GET[$pathInfoParams[$i]] = $paramsVal;
                        $_REQUEST[$pathInfoParams[$i]] = $paramsVal;  //将pathinfo地址模式匹配获取的参数压入$_GET里作为接受的参数
                    }
                }

            } else {
                if (isset($_SERVER['QUERY_STRING'])) {
                    if (Framework::$app->routeRule != 2) {
                        $validate = false;
                    }
                    $m = $c = $a = '';
                    $classControllerParams = array('m', 'c', 'a');
                    $pathInfo = explode('&', $_SERVER['QUERY_STRING']);
                    //先检查项目模块化配置
                    if ($projectType == 1) { //模块化
                        if (count($pathInfo) < 3) {
                            $validate = false;
                        }
                        $offset = 3;
                    } else { //非模块化
                        if (count($pathInfo) < 2) {
                            $validate = false;
                        }
                    }
                    parse_str($_SERVER['QUERY_STRING'], $urlDatas);
                    if (!$urlDatas) {
                        $validate = false;
                    }
                    $paramArr = array();
                    foreach ($urlDatas as $key => $val) {
                        if (in_array($key, $classControllerParams)) { //地址变量参数
                            $$key = $val;
                            $validateValResult = self::validate($paramsValPattern, $val);
                            if ($validateValResult) {
                                $validate = false;
                            }
                            unset($_GET[$key]);
                            unset($_REQUEST[$key]);
                        } else { //参数压入请求
                            $validateParamKeyResult = self::validate($pattern, $key);
                            $validateParamValResult = self::validate($paramsValPattern, $val);
                            if (!$validateParamKeyResult || $validateParamValResult) {
                                unset($_GET[$key]);
                                unset($_REQUEST[$key]);
                            }
                            $paramsVal = $validateParamValResult == true ? htmlspecialchars(addslashes(trim($val))) : $val;
                            ($validateParamKeyResult == true && $key) && $_GET[$key] = $paramsVal;
                            $_REQUEST[$key] = $paramsVal;  //将pathinfo地址模式匹配获取的参数压入$_GET里作为接受的参数
                            $validateParamKeyResult && $validateParamValResult && $paramArr[$key] = $val;
                        }
                    }
                    $module = $controller = $action = '';
                    $m && $module = $m;
                    $c && $controller = $c;
                    $a && $action = $a;
                    if ($projectType == 1) { //模块化
                        if (!$module) {
                            $validate = false;
                        }
                    }

                }
            }
            if (!$validate) {
                $_module = Framework::$app->default_module;
                $_controller = Framework::$app->default_controller;
                $_action = Framework::$app->default_action;
            }
            $_module = $module ? $module : false;
            $_controller = $controller ? $controller : false;
            $_action = $action ? $action : false;
            if ($projectType == 1) { //模块化
                if (!$_module || !$_controller || !$_action) {
                    $_module = Framework::$app->default_module;
                    $_controller = Framework::$app->default_controller;
                    $_action = Framework::$app->default_action;
                }
            } else {
                if (!$_controller || !$_action) {
                    $_controller = Framework::$app->default_controller;
                    $_action = Framework::$app->default_action;
                }
            }
            $_module && $_module = ucfirst($_module);
            $_module && Framework::$app->current_module = $_module;
            $_controller && $_controller = ucfirst($_controller);
            $_controller && Framework::$app->current_controller = $_controller;
            $_action && Framework::$app->current_action = $_action;
            $_module && $urlModule = $_module;
            $urlController = $_controller . "Controller";
            $urlAction = $_action . "Action";
            if ($projectType == 1) { //模块化
                $classNameSpacePath = sprintf("\\%s\\Modules\\%s\\Controller\\%s", $appNameSpace, $urlModule, $urlController);
            } else {
                $classNameSpacePath = sprintf("\\%s\\Controller\\%s", $appNameSpace, $urlController);
            }
            $classObject = new $classNameSpacePath();
            $controllerInstance = new \ReflectionClass($classNameSpacePath);
            if ($controllerInstance->hasMethod($urlAction)) {
                $method = new \ReflectionMethod($classNameSpacePath, $urlAction);
                if ($method->isPublic() && !$method->isStatic()) {
                    try {
                        //检测控制器初始化方法
                        if ($controllerInstance->hasMethod('init')) {
                            $initMethod = new \ReflectionMethod($classNameSpacePath, 'init');
                            if ($initMethod->isPublic()) {
                                $initMethod->invoke($classObject);
                            }
                        }
                        //前置Action初始化方法
                        if ($controllerInstance->hasMethod('__beforeAction')) {
                            $beforeActionMethod = new \ReflectionMethod($classNameSpacePath, '__beforeAction');
                            if ($beforeActionMethod->isPublic()) {
                                $beforeActionMethod->invoke($classObject);
                            }
                        }
                        $method->invoke($classObject);
                        //后置Action初始化方法
                        if ($controllerInstance->hasMethod('__afterAction')) {
                            $afterActionMethod = new \ReflectionMethod($classNameSpacePath, '__afterAction');
                            if ($afterActionMethod->isPublic()) {
                                $afterActionMethod->invoke($classObject);
                            }
                        }
                    } catch (\ReflectionException $e) {
                        // 方法调用发生异常后 引导到__call方法处理
                        $method = new \ReflectionMethod($classNameSpacePath, '__call');
                        $method->invokeArgs($classObject, array($urlAction, ''));
                    } catch (\Throwable $t) {
                        $msg = 'Fatal error: ' . $t->getMessage() . ' on ' . $t->getFile() . ' on line ' . $t->getLine();
                        // 触发错误异常
                        throw new \Exception($msg, 1);
                    }
                } else {
                    throw new \Exception('class method ' . $urlAction . ' is static or private, protected property, can not be object call!', 1);
                }
            } else {
                throw new \Exception("404");
            }
        } catch (\Exception $e) {
            // 方法调用发生异常后 引导到__call方法处理
            throw new \Exception($e->getMessage(), 1);
        }
    }

    //验证地址当中的一些参数防止注入
    public static function validate($pattern, $params)
    {
        $return = preg_match($pattern, $params);
        return $return;
    }




    /**
     * checkClass 检查请求实例文件是否存在
     * @param string $class
     * @return boolean
     */
    public static function checkClass($class)
    {
        $path = str_replace('\\', '/', $class);
        $path = trim($path, '/');
        $file = ROOT_PATH . DIRECTORY_SEPARATOR . $path . '.php';
        if (is_file($file)) {
            self::$routeCacheFileMap[$class] = true;
            return true;
        }
        return false;
    }

    //安全过滤函数防止XSS
    public static function filter()
    {
        if (is_array($_SERVER)) {
            foreach ($_SERVER as $k => $v) {
                if (isset($_SERVER[$k])) {
                    $_SERVER[$k] = str_replace(array('<', '>', '"', "'", '%3C', '%3E', '%22', '%27', '%3c', '%3e'), '', $v);
                }
            }
        }
        unset($_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
        self::filter_slashes($_GET);
        self::filter_slashes($_POST);
        self::filter_slashes($_COOKIE);
        self::filter_slashes($_FILES);
        self::filter_slashes($_REQUEST);
    }

    /**
     * 安全过滤类-加反斜杠，放置SQL注入
     * @param string $value 需要过滤的值
     * @return string
     */
    public static function filter_slashes(&$value)
    {
        if (get_magic_quotes_gpc()) return false; //开启魔术变量
        $value = (array)$value;
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                self::filter_slashes($value[$key]);
            } else {
                $value[$key] = addslashes($val);
            }
        }
    }

}
