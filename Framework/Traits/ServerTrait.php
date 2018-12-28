<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/27
 * Time: 15:39
 */
namespace Framework\Traits;
use Framework\SwServer\ServerManager;

trait ServerTrait
{
    /**
     * getMasterId 获取当前服务器主进程的PID
     * @return   int
     */
    public static function getMasterPid()
    {
        return ServerManager::getSwooleServer()->master_pid;
    }

    /**
     * getManagerId 获取当前服务器管理进程的PID
     * @return   int
     */
    public static function getManagerPid()
    {
        return ServerManager::getSwooleServer()->manager_pid;
    }

    /**
     * getCurrentWorkerPid 获取当前worker的进程PID
     * @return int
     */
    public static function getCurrentWorkerPid()
    {
        $workerPid = ServerManager::getSwooleServer()->worker_pid;
        if ($workerPid) {
            return $workerPid;
        } else {
            return posix_getpid();
        }
    }

    /**
     * getCurrentWorkerId 获取当前处理的worker_id
     * @return   int
     */
    public static function getCurrentWorkerId()
    {
        $workerId = ServerManager::getSwooleServer()->worker_id;
        return $workerId;
    }

    /**
     * getConnections 获取服务器当前所有的连接
     * @return  object
     */
    public static function getConnections()
    {
        return ServerManager::getSwooleServer()->connections;
    }

    /**
     * getWorkersPid 获取当前所有worker_pid与worker的映射
     * @return   array
     */
    public static function getWorkersPid()
    {
        return BaseServer::getWorkersPid();
    }

    /**
     * getLastError 获取最近一次的错误代码
     * @return   int
     */
    public static function getLastError()
    {
        return ServerManager::getSwooleServer()->getLastError();
    }

    /**
     * getStats 获取swoole的状态
     * @return   array
     */
    public static function getSwooleStats()
    {
        return ServerManager::getSwooleServer()->stats();
    }

    /**
     * getLocalIp 获取ip,不包括端口
     * @return   array
     */
    public static function getLocalIp()
    {
        return swoole_get_local_ip();
    }

    /**
     * getIncludeFiles 获取swoole启动时,worker启动前已经include内存的文件
     * @return   array|boolean
     */
    public static function getInitIncludeFiles($dir = 'http')
    {
        // 获取当前的处理的worker_id
        $workerId = self::getCurrentWorkerId();
        if (isset(ServerManager::$config['log'])) {
            $path = ServerManager::$config['log']['log_dir'];
            $dir = strtolower($dir);
            $filePath = $path . DIRECTORY_SEPARATOR . $dir . '/includes.txt';
        } else {
            $dir = ucfirst($dir);
            $filePath = __DIR__ . '/../' . $dir . '/includes.json';
        }
        if (is_file($filePath)) {
            $includes_string = file_get_contents($filePath);
            if ($includes_string) {
                return [
                    'current_worker_id' => $workerId,
                    'include_init_files' => json_decode($includes_string, true),
                ];
            } else {
                return false;
            }
        }

        return false;

    }

    /**
     * getMomeryIncludeFiles 获取执行到目前action为止，swoole server中的该worker中内存中已经加载的class文件
     * @return  array
     */
    public static function getMomeryIncludeFiles()
    {
        $includeFiles = get_included_files();
        $workerId = self::getCurrentWorkerId();
        return [
            'current_worker_id' => $workerId,
            'include_momery_files' => $includeFiles,
        ];
    }


    /**
     * getAppConfig 获取应用层配置
     * @return   array
     */
    public static function getAppConf()
    {
        return ServerManager::$config;
    }


    /**
     * isWorkerProcess 进程是否是worker进程
     * @param    $worker_id
     * @return   boolean
     */
    public static function isWorkerProcess()
    {
        return (!self::isTaskProcess()) ? true : false;
    }

    /**
     * isTaskProcess 进程是否是task进程
     * @param    $worker_id
     * @return   boolean
     */
    public static function isTaskProcess()
    {
        $server = ServerManager::getSwooleServer();
        if (property_exists($server, 'taskworker')) {
            return $server->taskworker;
        }
        throw new \Exception("not found task process,may be you use it before workerStart()", 1);
    }

    /**
     * getServer 获取server对象
     * @return   object
     */
    public static function getServer()
    {
        if (is_object(ServerManager::$server)) {
            return ServerManager::$server;
        }
        return NULL;
    }
}