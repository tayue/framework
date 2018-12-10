<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/29
 * Time: 10:34
 */

namespace Framework\SwServer\Process;

use Framework\Tool\PluginManager;
use Framework\SwServer\Process\Interfaces\ProcessMessageInterface;


class ProcessMessage implements ProcessMessageInterface
{
    public $hook;
    private $messageData;
    private $messageParams;

    public function setHook($hook)
    {
        if ($hook) {
            $isHasHook = PluginManager::getInstance()->hasHook($hook);
            if ($isHasHook) {
                $this->hook = $hook;
            } else {
                throw new \Exception("Hook Not Exists !");
            }
        }

    }

    public function getHook()
    {
        return $this->hook;
    }

    public function setMessageData($messageData)
    {
        $this->messageData = $messageData;
    }

    public function getMessageData()
    {
        return $this->messageData;
    }

    public function getMessageParams()
    {
        return $this->messageParams;
    }

    public function setMessageParams(...$messageParams)
    {
        $this->messageParams = $messageParams;
    }

}