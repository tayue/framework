<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 17:39
 */

namespace Framework\SwServer\Pool;

use Framework\Core\Exception;
use \Swoole\Coroutine\Redis;

class RedisPoolManager extends PoolBase
{
    use \Framework\Traits\SingletonTrait;
    public $poolObject = '';
    public $default_config = [
        'host' => '192.168.99.88',   //ip
        'port' => 6379,          //端口
        'timeout' => 1.5,
        'database' => 1,
        'password' => '', //密码
        'space_time'=>100,
        'mix_pool_size' => 2,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ];

    public function checkConnection()
    {
        try {
            $redis = new Redis();
            $res = $redis->connect($this->config['host'], $this->config['port']);
            if ($res == false) {
                //连接失败，抛弃常
                throw new Exception("failed to connect redis server.");
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\r\n";
            return false;
        }
        return $redis;
    }

    private function __construct($config = [])
    {
        if ($config) {
            $this->config = array_merge($this->default_config, $config);
        } else {
            $this->config = $this->default_config;
        }
        parent::__construct($this->config);
    }
}