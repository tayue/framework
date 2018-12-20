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
use Framework\Core\Route;
use Framework\Web\Application;
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
        ServerManager::$app = (new Application($this->config));
        ServerManager::$app->run($this->config);
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
            //浏览器会自动发起这个请求，这也是很多人碰到的一个问题：
            //为什么我浏览器打开网站，收到了两个请求?
            if ($request->server['path_info'] == '/favicon.ico') {
                $response->end('');
                return;
            }
            $this->fd = $request->fd;
            if ($request->server['request_uri']) { //请求地址
                Route::parseSwooleRouteUrl($request, $response);
            }
        } catch (\Throwable $t) {
            CustomerError::writeErrorLog($t);

        }
    }


}