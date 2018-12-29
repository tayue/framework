<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 17:09
 */

namespace Framework\SwServer;




class ServerApplication extends BaseServerApplication
{
    public function run(\swoole_http_request $request, \swoole_http_response $response)
    {
        print_r(ServerManager::$app);
        $this->init();
        $this->parseUrl($request, $response);
    }
}