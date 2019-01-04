<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:18
 */

namespace Framework\SwServer\Base;

use Framework\Core\Db;
use Framework\SwServer\ServerManager;


class Application extends \Framework\SwServer\Base\BaseApplication
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