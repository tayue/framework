<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/14
 * Time: 13:40
 */

namespace App\Service;


use Framework\SwServer\ServerManager;
use Framework\SwServer\WebSocket\WST;

class User
{
    public $arr = [];

    public function findUser()
    {
        $userData = ServerManager::getApp()->db->table('user')->find();
        return $userData;
    }

}