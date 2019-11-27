<?php


namespace Framework\Core;

use Swoole\Coroutine\MySQL;

class MysqlCoroutine
{
    public $connection;

    public function __construct($config)
    {
        try {
            $this->connection = new MySQL();
            $res = $this->connection->connect($config);
            if ($res == false) {
                //连接失败，抛弃常
                throw new \Exception("Failed to Connect Coroutine Mysql Server.");
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