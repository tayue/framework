<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 15:11
 */

namespace Framework\SwServer\Pool;

class PoolBase implements Pool
{
    public $pool;
    public $config;

    /**
     * MysqlPool constructor.
     * @param $config
     * @desc 初始化，自动创建实例,需要放在workerstart中执行
     */
    public function __construct($config)
    {
        if (empty($this->pool)) {
            $this->config = $config;
            $this->pool = new \Swoole\Coroutine\Channel($config['pool_size']);
            $poolSize = (isset($this->config['pool_size']) && $this->config['pool_size']) ? $this->config['pool_size'] : 5;
            $this->initPool($poolSize);
        }
    }

    /**
     * @param $mysql
     * @desc 放入一个mysql连接入池
     */
    public function put($data)
    {
        $flag = false;
        if ($data) {
            $flag = $this->pool->push($data);
        }
        return $flag;

    }

    /**
     * @return mixed
     * @desc 获取一个连接，当超时，返回一个异常
     */
    public function get()
    {
        try {
            $resource = $this->pool->pop($this->config['pool_get_timeout']);
            if (false === $resource) {
                throw new \Exception("get resource timeout, all resource connection is used");
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return $resource;
    }


    /**
     * @return mixed
     * @desc 获取当时连接池可用对象
     */
    public function getLength()
    {
        return $this->pool->length();
    }

    public function initPool($poolSize = 10)
    {
        try {
            for ($i = 0; $i < $poolSize; $i++) {
                $res = $this->checkConnection();
                if ($res == false) {
                    //连接失败，抛弃常
                    throw new Exception("failed to connect resource server.");
                } else {
                    //resource连接存入channel
                    $this->put($res);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
        } catch (\Throwable $t) {
            echo $t->getMessage() . "\r\n";
        }
    }

}