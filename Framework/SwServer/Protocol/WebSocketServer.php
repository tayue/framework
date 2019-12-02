<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/25
 * Time: 17:04
 */

namespace Framework\SwServer\Protocol;
use Framework\SwServer\ServerManager;
use Framework\Tool\Log;
use Swoole\WebSocket\Server as websocket_server;

class WebSocketServer extends WebServer implements WebsocketProtocol
{
    public function __construct($config)
    {
        parent::__construct($config);

    }

    public function createServer()
    {
        self::$server = new websocket_server($this->config['server']['listen_address'], $this->config['server']['listen_port'], self::$swoole_process_mode, self::$swoole_socket_type);
        self::$server->set($this->setting);
        Log::getInstance()->setConfig($this->config);
        $this->setLogger(Log::getInstance());
        return self::$server;
    }

    /**
     * onMessage 接受信息并处理信息
     * @param object $server
     * @param object $frame
     * @return   void
     */
    public function onMessage($server, $frame)
    {
        $fd = $frame->fd;
        $messageData = $frame->data;
        $opcode = $frame->opcode;
        $finish = $frame->finish;
        // 数据接收是否完整
        if ($finish) {
            // utf-8文本数据
            if ($opcode == WEBSOCKET_OPCODE_TEXT) {
                $serverApp = \unserialize(ServerManager::$serverApp);
                $serverApp->webSocketRun($fd, $messageData);

            } else if ($opcode == WEBSOCKET_OPCODE_BINARY) {
                // TODO 二进制数据

            } else if ($opcode == 0x08) {
                // TODO 关闭帧

            }

        } else {
            // 断开连接
            $server->close();
        }

    }

    public function onOpen($server, $request)
    {
        echo "server#{$server->worker_pid}: handshake success with fd#{$request->fd}\n";
        //var_dump($server->exist($request->fd), $server->getClientInfo($request->fd));
    }

    function onClose($server, $client_id, $from_id)
    {
        // TODO: Implement onClose() method.
    }

}