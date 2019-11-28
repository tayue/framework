<?php
/**
 * +----------------------------------------------------------------------
 * | swoolefy framework bases on swoole extension development, we can use it easily!
 * +----------------------------------------------------------------------
 * | Licensed ( https://opensource.org/licenses/MIT )
 * +----------------------------------------------------------------------
 * | Author: bingcool <bingcoolhuang@gmail.com || 2437667702@qq.com>
 * +----------------------------------------------------------------------
 */

namespace Framework\Core;

use Predis\Client;

class Redis
{

    /**
     * $config 配置
     * @var array
     */
    public $config = [];

    public $redis;


    /**
     * $default_config 默认的配置项
     * @var [type]
     */
    protected $default_config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 1
    ];

    /**
     * $corRedis redis的协程客户端
     * @var [type]
     */
    public $CRedis;


    /**
     * __construct 初始化函数
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->config = array_merge($this->default_config, $this->config, $config);
        }
        $this->redis = new Client($this->config);
    }

    public function getConnection()
    {
        return $this->redis;
    }

    /**
     * getConfig 获取某个配置项
     * @param string $name
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * CorRedis 切换至redis协程客户端
     * @param array $extension
     * @return   CRedis
     */
    public function selectRedis(array $extension = [])
    {
        if (is_object($this->redis)) {
            unset($this->redis);
        }
        $this->redis = new RedisCoroutine($this->getConfig(), $extension);

        return $this->redis;
    }

    /**
     * setConfig 设置配置项
     * @param array $config
     */
    public function setConfig(array $config = [])
    {
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }
    }


    /**
     * __call
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public function __call($method, $args)
    {

    }


}