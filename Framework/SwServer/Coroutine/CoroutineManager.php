<?php

namespace Framework\SwServer\Coroutine;

use Framework\SwServer\BaseServer;
use Swoole\Coroutine as SwCoroutine;
use Framework\Tool\Tool;
use Framework\Traits\SingletonTrait;

class CoroutineManager
{

    use SingletonTrait;
    private static $tid = -1;
    private static $idMap = [];

    /**
     * coroutine_id的前缀
     */
    const  PREFIX_CID = 'cid_';

    /**
     * $cid
     * @var null
     */
    protected static $cid = null;

    /**
     * isEnableCoroutine
     * @return   boolean
     */
    public function canEnableCoroutine()
    {
        return BaseServer::canEnableCoroutine();
    }

    /**
     * getMainCoroutineId 获取协程的id
     * @return
     */
    public function getCoroutineId()
    {
        // 大于4.x版本,建议使用版本
        if ($this->canEnableCoroutine()) {
            $cid = SwCoroutine::getuid();
            // 在task|process中不直接支持使用协程
            if ($cid == -1) {
                $cid = self::PREFIX_CID . 'task_process';
            } else {
                $cid = self::PREFIX_CID . $cid;
            }
            return $cid;
        } else {
            // 1.x, 2.x版本不能使用协程，2.x编译时需要关闭协程选项
            if (isset(self::$cid) && !empty(self::$cid)) {
                return self::$cid;
            }
            $cid = (string)time() . '_' . mt_rand(1, 999);
            self::$cid = self::PREFIX_CID . $cid;
            return self::$cid;
        }
    }

    /**
     * getCoroutinStatus
     * @return   array
     */
    public function getCoroutineStatus()
    {
        // 大于4.x版本
        if ($this->canEnableCoroutine()) {
            if (method_exists('co', 'stats')) {
                return \co::stats();
            }
        }
        // 1.x, 2.x版本
        return null;

    }

    /**
     * listCoroutines 遍历当前进程内的所有协程(swoole4.1.0+版本支持)
     * @return Iterator
     */
    public function listCoroutines()
    {
        if (method_exists('Swoole\Coroutine', 'listCoroutines')) {
            $cids = [];
            $coros = \Swoole\Coroutine::listCoroutines();
            foreach ($coros as $cid) {
                array_push($cids, $cid);
            }
            return $cids;
        }
        return null;
    }

    /**
     * getBackTrace 获取协程函数调用栈
     * @param   $cid
     * @param   $options
     * @param   $limit
     * @return  array
     */
    public function getBackTrace($cid = 0, $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0)
    {
        if (method_exists('Swoole\Coroutine', 'getBackTrace')) {
            return \Swoole\Coroutine::getBackTrace($cid, $options, $limit);
        }
        return null;
    }

    /**
     * Get the current coroutine ID,
     * Return null when running in non-coroutine context
     *
     * @return int|null
     */
    public static function id()
    {
        $cid = SwCoroutine::getuid();
        if ($cid !== -1) {
            return $cid;
        }

        return self::$tid;
    }

    /**
     * Get the top coroutine ID,
     * Return null when running in non-coroutine context
     *
     * @return int|null
     */
    public static function tid()
    {
        $id = self::id();
        return self::$idMap[$id] ?? $id;
    }

    /**
     * Create a coroutine
     *
     * @param callable $cb
     *
     * @return bool
     */
    public static function create(callable $cb)
    {
        $tid = self::tid();
        return SwCoroutine::create(function () use ($cb, $tid) {
            $id = SwCoroutine::getuid();
            self::$idMap[$id] = $tid;
            Tool::call($cb);
        });
    }

    /**
     * Suspend a coroutine
     *
     * @param string $corouindId
     */
    public static function suspend($corouindId)
    {
        SwCoroutine::suspend($corouindId);
    }

    /**
     * Resume a coroutine
     *
     * @param string $coroutineId
     */
    public static function resume($coroutineId)
    {
        SwCoroutine::resume($coroutineId);
    }

    public static function getIdMap()
    {
        return self::$idMap;
    }


}