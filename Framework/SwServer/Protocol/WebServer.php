<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 10:26
 */

namespace Framework\SwServer\Protocol;

use Framework\SwServer\BaseServer;
use Framework\SwServer\Protocol\Protocol;
use Framework\Tool\Log;
use Framework\Core\Route;

class WebServer extends BaseServer
{
    const SOFTWARE = "TayueWebServer";
    const POST_MAXSIZE = 2000000; //POST最大2M
    const DEFAULT_PORT = 9501;
    const DEFAULT_HOST = '0.0.0.0';
    public $fd;



    public $config = [
        'host' => self::DEFAULT_HOST,
        'port' => self::DEFAULT_PORT,

    ];


    public function __construct($config)
    {
        $this->config = array_merge($config, $this->config);
        parent::__construct($config);
        $this->host = $this->config['host'];
        $this->port = $this->config['port'];
        self::$isWebServer = true;
        if (isset($config['setting']) && $config['setting']) {
            $this->setting = array_merge($this->default_setting, $config['setting']);
        } else {
            $this->setting = $this->default_setting;
        }

    }

    public function createServer()
    {
        self::$server = new \swoole_http_server($this->config['host'], $this->config['port'], self::$swoole_process_mode, self::$swoole_socket_type);
        self::$server->set($this->setting);
        Log::getInstance()->setConfig($this->config);
        $this->setLogger(Log::getInstance());
        return self::$server;
    }

    public function getServer()
    {
        return self::$server;
    }


    function onStart($server)
    {
         echo "WebServer onStart\r\n";
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

       $this->fd=$request->fd;

       if($request->server['request_uri']){ //请求地址
           Route::parseSwooleRouteUrl($request,$response);
       }




    }


}