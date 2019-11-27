<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 15:11
 */

namespace Framework\SwServer\Pool;
use Swoole\Coroutine\Channel;

class PoolBase implements Pool
{
    public $pool;
    public $config;
    private $min = 5;//最少连接数
    private $max = 10;//最大连接数
    private $currentConnectionNum;//当前连接数
    protected $spaceTime = 10 * 3600;//用于空闲连接回收判断

    /**
     * MysqlPool constructor.
     * @param $config
     * @desc 初始化，自动创建实例,需要放在workerstart中执行
     */
    public function __construct($config)
    {
        if (empty($this->pool)) {
            $this->config = $config;
            (isset($this->config['mix_pool_size']) && $this->config['mix_pool_size']) && $this->min = $this->config['mix_pool_size'];
            (isset($this->config['max_pool_size']) && $this->config['max_pool_size']) && $this->max = $this->config['max_pool_size'];
            (isset($this->config['space_time']) && $this->config['space_time']) && $this->spaceTime = $this->config['space_time'];
            $this->pool = new Channel($this->max + 1); //最大+1预留多一个空间
            $this->initPool();
        }
    }

    /**
     * @param $data
     * @desc 放入一个Resource连接入池
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
    public function get($timeout = '')
    {
        $timeout = $timeout ? $timeout : $this->config['pool_get_timeout'];
        try {
            if ($this->pool->isEmpty()) { //剩余资源数为空
                if ($this->currentConnectionNum < $this->max) { //当前资源连接数小于最大连接数时可以继续创建资源
                    $resourceData = $this->createResource();
                    $this->currentConnectionNum++;
                } else {
                    $resourceData = $this->pool->pop($timeout); //如果连接池子的连接资源都已经耗尽且当前资源连接数达到最大值，那么此时阻塞等待资源入池
                }
            } else {
                $resourceData = $this->pool->pop($timeout);
            }
            if (!$resourceData) {
                throw new \Exception("get resource timeout\r\n");
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return $resourceData;
    }

    /**
     * @return mixed
     * @desc 获取当时连接池可用对象
     */
    public function getLength()
    {
        return $this->pool->length();
    }

    /**
     * @return mixed
     * @desc 获取当前连接数
     */
    public function getCurrentConnectionNums()
    {
        return $this->currentConnectionNum;
    }

    public function initPool()
    {
        try {
            for ($i = 0; $i < $this->min; $i++) {
                $resourceObj = $this->createResource();
                $this->currentConnectionNum++;
                $this->put($resourceObj);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
        } catch (\Throwable $t) {
            echo $t->getMessage() . "\r\n";
        }
    }

    /**
     * @desc 清理空闲连接资源
     */
    public function clearSpaceResources()
    {
        //大约2分钟检测一次连接资源数
        swoole_timer_tick(120000, function () {
            $list = [];
            $currentResourceLength = $this->getLength(); //资源池剩余连接数
            if ($currentResourceLength < intval($this->max * 0.5)) {
                echo "There are still more requests to connect, and idle connections are not reclaimed.\n";
            }
            echo "------Start Colletion Idle Connections[{$currentResourceLength}]------" . PHP_EOL;
            while (true) {
                if (!$this->pool->isEmpty()) {
                    $obj = $this->pool->pop(0.001);
                    $last_used_time = $obj['last_used_time'];
                    $diffTimes = time() - $last_used_time;
                    if ($this->currentConnectionNum > $this->min && ($diffTimes > $this->spaceTime)) {//回收
                        $this->currentConnectionNum--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $eachResourceData) {
                $this->put($eachResourceData);
            }
            $currentResourceLength = $this->getLength();
            echo "------End Colletion Idle Connections[{$currentResourceLength}]------" . PHP_EOL;
        });
    }

    /**
     * @desc 创建连接资源
     */
    public function createResource()
    {
        $resourceData = null;
        $res = $this->checkConnection();
        if ($res == false) {
            //连接失败，抛弃常
            throw new Exception("failed to connect resource server.");
        } else {
            $resourceData = [
                'last_used_time' => time(),
                'resource' => $res
            ];
        }
        return $resourceData;
    }

}