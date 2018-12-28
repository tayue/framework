<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/27
 * Time: 17:00
 */

namespace Framework\SwServer\Common;

use Framework\SwServer\ServerManager;

class ProtocolCommon
{
    public static function sender($fd, $message, $code=404,$data='')
    {
        if (ServerManager::getServiceProtocol() == SWOOLE_WEBSOCKET) {
            $data = ['code' => $code, 'msg' => $message, 'data' => $data];
            ServerManager::getSwooleServer()->push($fd, $data, $opcode = 1, $finish = true);
        } else if ((ServerManager::getServiceProtocol() == SWOOLE_TCP)) {
            $data = ['code' => $code, 'msg' => $message, 'data' => $data];
            ServerManager::getSwooleServer()->send($fd, $data);
        }


    }

}