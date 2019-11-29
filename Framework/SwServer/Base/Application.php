<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:18
 */

namespace Framework\SwServer\Base;

use Framework\Core\Db;
use Framework\SwServer\ServerManager;
use Framework\SwServer\Base\BaseApplication;

class Application extends BaseApplication
{

    public function run($fd, $messageData, $isTcpApp = true)
    {
        $this->fd = $fd;
        $this->init();
        if ($isTcpApp) {
            $this->parseTcpRoute($messageData);
        } else {
            $this->parseRoute($messageData);
        }
    }


}