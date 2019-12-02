<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 17:09
 */

namespace Framework\SwServer;

class ServerApplication extends AbstractServerApplication
{
    public function run($fd, \swoole_http_request $request, \swoole_http_response $response)
    {
        $this->fd = $fd;
        $this->init();
        $this->parseUrl($request, $response);
    }

    public function tcpRun($fd, $recv)
    {
        $this->fd = $fd;
        $this->init();
        $this->parseTcpRoute($recv);
    }

    public function webSocketRun($fd, $messageData)
    {
        $this->fd = $fd;
        $this->init();
        $this->parseRoute($messageData);
    }
}