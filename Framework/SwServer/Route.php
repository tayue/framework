<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/8
 * Time: 16:02
 */

namespace Framework\SwServer;

use Framework\Core\DependencyInjection;
use Framework\SwServer\Common\ProtocolCommon;
use Framework\SwServer\ServerManager;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\RateLimit\RateLimit;


class Route
{

    public static $routeCacheFileMap;


    //验证地址当中的一些参数防止注入
    public static function validate($pattern, $params)
    {
        $return = preg_match($pattern, $params);
        return $return;
    }

    public static function parseSwooleRouteUrl(\swoole_http_request $request, \swoole_http_response $response)
    {
        try {
            $msg = '';
            $request_uri = $request->server['request_uri'];
            $validate = true;
            $projectType = ServerManager::getApp()->project_type;
            $pattern = '/^[0-9a-zA-Z]+$/'; //验证一些参数
            $paramsValPattern = '/[^0-9a-zA-Z]/'; //验证地址参数值(除了字符以外的任意参数)
            $appNameSpace = ServerManager::getApp()->project_namespace;
            $_module = $_controller = $_action = '';
            //如果使用了pathinfo的模式的话用pathinfo模式去解析url地址的参数
            if (ServerManager::getApp()->routeRule != 1) {
                $validate = false;
            }
            $pathInfo = explode('/', trim($request_uri, '/'));
            $offset = 2;
            $module = ServerManager::getApp()->default_module;
            $controller = ServerManager::getApp()->default_controller;
            $action = ServerManager::getApp()->default_action;
            //先检查项目模块化配置
            if ($projectType == 1) { //模块化
                if (count($pathInfo) < 3) {
                    $validate = false;
                } else {
                    @list($module, $controller, $action) = $pathInfo;
                    $moduleValidate = self::validate($pattern, $module);
                    if (!$moduleValidate || !$module) {
                        $validate = false;
                    }
                }
                $offset = 3;
            } else { //非模块化
                if (count($pathInfo) < 2) {
                    $validate = false;
                } else {
                    list($controller, $action) = $pathInfo;
                }
            }

            $controllerValidate = self::validate($pattern, $controller);
            $actionValidate = self::validate($pattern, $action);
            if (!$controllerValidate || !$actionValidate) {
                $validate = false;
            }
            if (!$validate) {
                $_module = ServerManager::getApp()->default_module;
                $_controller = ServerManager::getApp()->default_controller;
                $_action = ServerManager::getApp()->default_action;
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
            if ($request->server['request_method'] == 'POST') { //POST请求
                $_POST = $request->post;
                $_REQUEST = array_merge($_REQUEST, $_POST);
            }

            if (!$validate) {
                $_module = ServerManager::getApp()->default_module;
                $_controller = ServerManager::getApp()->default_controller;
                $_action = ServerManager::getApp()->default_action;
            }
            $_module = $module ? $module : false;
            $_controller = $controller ? $controller : false;
            $_action = $action ? $action : false;
            if ($projectType == 1) { //模块化
                if (!$_module || !$_controller || !$_action) {
                    $_module = ServerManager::getApp()->default_module;
                    $_controller = ServerManager::getApp()->default_controller;
                    $_action = ServerManager::getApp()->default_action;
                }
            } else {
                if (!$_controller || !$_action) {
                    $_controller = ServerManager::getApp()->default_controller;
                    $_action = ServerManager::getApp()->default_action;
                }
            }

            $coroutineId = ServerManager::getApp()->coroutine_id;
            $_module && $_module = ucfirst($_module);
            $_module && ServerManager::$app[$coroutineId]->current_module = $_module;
            $_controller && $_controller = ucfirst($_controller);
            $_controller && ServerManager::$app[$coroutineId]->current_controller = $_controller;
            $_action && ServerManager::$app[$coroutineId]->current_action = $_action;
            $_module && $urlModule = $_module;
            $urlController = $_controller . "Controller";
            $urlAction = $_action . "Action";
            if ($projectType == 1) { //模块化
                $classNameSpacePath = sprintf("%s\\Modules\\%s\\Controller\\%s", $appNameSpace, $urlModule, $urlController);
            } else {
                $classNameSpacePath = sprintf("%s\\Controller\\%s", $appNameSpace, $urlController);
            }

            if (\method_exists($classNameSpacePath, $urlAction)) {
                $method = new \ReflectionMethod($classNameSpacePath, $urlAction);
                if ($method->isPublic() && !$method->isStatic()) {
                    try {
                        $res=RateLimit::getInstance()->minLimit($classNameSpacePath."::".$urlAction,function (){
                            echo "Rate Limit:".date("Y-m-d H:i:s")."\r\n";
                        });
                        if(!$res['flag']){
                            throw  new \Exception($res['msg']."\r\n");
                        }
                        DependencyInjection::make($classNameSpacePath, $urlAction);
                    } catch (\ReflectionException $e) {
                        throw new \Exception($e->getMessage(), 1);
                    } catch (\Throwable $t) { //将致命错误捕捉到进行错误类型转换
                        $msg = 'Fatal error: ' . $t->getMessage() . ' on ' . $t->getFile() . ' on line ' . $t->getLine();
                        throw new \Exception($msg, 1);
                    }

                } else {
                    throw new \Exception('class method ' . $urlAction . ' is static or private, protected property, can not be object call!', 1);
                }
            } else {
                throw new \Exception("404");
            }
        } catch (\Exception $e) {
            $response->end($e->getMessage());
            throw new \Exception($e->getMessage(), 1);
        } catch (\Throwable $t) {
            $response->end($msg);
            throw new \Exception($t->getMessage(), 1);
        }

    }

    //解析服务类地址路由
    public static function parseServiceMessageRouteUrl($callable, $params)
    {
        try {
            $errorMessage = '';
            list($service, $operate) = $callable;
            $service = str_replace('/', '\\', $service);
            $isExists = self::checkClass($service);
            if ($isExists) {
                DependencyInjection::make($service, $operate, $params);
            } else {
                throw new \Exception("404");
                $errorMessage = "Service:{$service} Class Is Not Found !!";
                ProtocolCommon::sender(ServerManager::getInstance()->getApp()->fd, $errorMessage, 0);
            }

        } catch (\Exception $e) {
            ProtocolCommon::sender(ServerManager::getInstance()->getApp()->fd, $e->getMessage(), $e->getCode());
            throw new \Exception($e->getMessage(), 1);
        } catch (\Throwable $t) {
            ProtocolCommon::sender(ServerManager::getInstance()->getApp()->fd, $t->getMessage(), $t->getCode());
            throw new \Exception($t->getMessage(), 1);
        }

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
