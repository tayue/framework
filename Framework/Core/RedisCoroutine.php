<?php


namespace Framework\Core;

use Swoole\Coroutine\Redis;

class RedisCoroutine
{
    public $connection;

    public function __construct($config)
    {
        try {
            $this->connection = new Redis();
            $res = $this->connection->connect($config);
            if ($res == false) {
                //连接失败，抛弃常
                throw new \Exception("Failed to Connect Coroutine Redis Server.");
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
            return false;
        }

    }

    public function getConnection()
    {
        return $this->connection;
    }
}