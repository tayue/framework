<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/29
 * Time: 10:34
 */

namespace Framework\SwServer\Process\Interfaces;


Interface ProcessMessageInterface
{
    public function setHook($hook);

    public function getHook();

    public function setMessageData($messageData);

    public function getMessageData();

    public function getMessageParams();

    public function setMessageParams(...$messageParams);


}