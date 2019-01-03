<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 13:55
 */

namespace Framework\SwServer\Base;


use Framework\SwServer\ServerManager;
use Framework\SwServer\WebSocket\WST;

class BaseServerEvent
{
    public $mixedParams;

    public function push($data = [], $fd = 0)
    {
        $currentFd = WST::getInstance()->getApp()->fd;
        if ($fd) {
            $currentFd = $fd;
        }
        if ($data) {
            $data = json_encode($data);
        }
        ServerManager::getSwooleServer()->push($currentFd, $data);
    }


}