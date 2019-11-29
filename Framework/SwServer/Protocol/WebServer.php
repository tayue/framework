<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 10:26
 */

namespace Framework\SwServer\Protocol;

use Framework\SwServer\BaseServer;
use Framework\Tool\Log;
use Framework\SwServer\ServerApplication;
use Framework\SwServer\ServerManager;
use Framework\Core\error\CustomerError;


class WebServer extends BaseServer
{

    const POST_MAXSIZE = 2000000; //POST最大2M
    public $fd;


    public function __construct($config)
    {
        parent::__construct($config);
        self::$isWebServer = true;
        $this->setSwooleSockType();
    }

    public function createServer()
    {
        self::$server = new \swoole_http_server($this->config['server']['listen_address'], $this->config['server']['listen_port'], self::$swoole_process_mode, self::$swoole_socket_type);
        self::$server->set($this->setting);
        Log::getInstance()->setConfig($this->config);
        $this->setLogger(Log::getInstance());
        return self::$server;
    }

    public function getServer()
    {
        return self::$server;
    }

    public function onMasterStart()
    {


    }

    function onStart($server)
    {
        echo "WebServer onStart\r\n";
    }

    function onWorkerStart($server, $worker_id)
    {
        //初始化应用层
        $app = new ServerApplication($this->config);
        ServerManager::$serverApp = \serialize($app);
    }

    function onWorkerStop($server, $worker_id)
    {

    }

    function onConnect($server, $client_id, $from_id)
    {
        // TODO: Implement onConnect() method.
    }

    function onReceive($server, $client_id, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    function onClose($server, $client_id, $from_id)
    {
        // TODO: Implement onClose() method.
    }

    function onShutdown($server)
    {

    }


    function onFinish(\swoole_server $serv, $task_id, $data)
    {
        echo "Task#$task_id finished, info=" . $data . PHP_EOL;
    }

    function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        try {
            if ($request->server['path_info'] == '/favicon.ico') {
                $response->end('');
                return;
            }
            ob_start();
            $this->fd = $request->fd;
            if ($request->server['request_uri']) { //请求地址
                $serverApp = \unserialize(ServerManager::$serverApp);
                $serverApp->run($this->fd,$request, $response);
            }
            ServerManager::destroy(); //销毁应用实例
            $out1 = ob_get_contents();
            ob_end_clean();
            $response->end($out1);
        } catch (\Throwable $t) {
            CustomerError::writeErrorLog($t);
        }
    }


}
