<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/25
 * Time: 16:59
 */
namespace Framework\SwServer\Protocol;
/**
 * websocket 定义接口
 */
interface WebsocketProtocol
{
    public function onOpen($server, $request);

    public function onMessage($server, $frame);

}