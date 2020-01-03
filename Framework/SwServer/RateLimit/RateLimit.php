<?php

namespace Framework\SwServer\RateLimit;

use Framework\SwServer\Pool\RedisPoolManager;
use Framework\Traits\SingletonTrait;

/**
 * 限流控制
 */
class RateLimit
{
    use SingletonTrait;
    private $minNum = 60; //单个用户每分访问数
    private $dayNum = 10000; //单个用户每天总的访问量
    public $redis;

    public function getRedisConnection($timeout = 0.5)
    {
        $resourceData = RedisPoolManager::getInstance()->get($timeout);
        if ($resourceData) {
            defer(function () use ($resourceData) {
                RedisPoolManager::getInstance()->put($resourceData);
                echo "[" . date('Y-m-d H:i:s') . "] Current Use Redis Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
            });
            $this->redis = $resourceData['resource'];
        }
        if (!$this->redis) {
            throw new \Exception("No Active Redis Connection !");
        }
        return $this->redis;
    }

    public function minLimit($uid, callable $callbackFunc = null)
    {
        $res = ['flag' => true, 'msg' => ''];
        $minNumKey = $uid . '_minNum';
        $dayNumKey = $uid . '_dayNum';
        $resMin = $this->getRedis($minNumKey, $this->minNum, 60);
        $resDay = $this->getRedis($dayNumKey, $this->dayNum, 86400);
        if (!$resMin['status'] || !$resDay['status']) {
            ($callbackFunc && is_callable($callbackFunc)) && $callbackFunc();
            $res['flag'] = false;
            $res['msg'] = $resMin['msg'] . " " . $resDay['msg'];
        }
        return $res;
    }

    public function getRedis($key, $initNum, $expire)
    {
        $nowtime = time();
        $result = ['status' => true, 'msg' => ''];
        $this->redis = $this->getRedisConnection();
        $this->redis->watch($key); //redis 乐观锁并发控制
        $limitVal = $this->redis->get($key);
        if ($limitVal) {
            $limitVal = json_decode($limitVal, true);
            $newNum = min($initNum, ($limitVal['num'] - 1) + (($initNum / $expire) * ($nowtime - $limitVal['time'])));
            if ($newNum > 0) {
                $redisVal = json_encode(['num' => $newNum, 'time' => time()]);
            } else {
                return ['status' => false, 'msg' => $key . " " . 'The token is consumed at the current time!'];
            }
        } else {
            $redisVal = json_encode(['num' => $initNum, 'time' => time()]);
        }
        $this->redis->multi();
        $this->redis->set($key, $redisVal);
        $rob_result = $this->redis->exec();
        if (!$rob_result) {
            $result = ['status' => false, 'msg' => $key . " " . 'Too many visits!'];
        }
        return $result;
    }
}
