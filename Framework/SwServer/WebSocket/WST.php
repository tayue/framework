<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:20
 */

namespace Framework\SwServer\WebSocket;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Coroutine\CoroutineModel;

class WST
{
    use \Framework\Traits\SingletonTrait;
    public $coroutine_id;
    public static $app;
    public $fd;

    /**
     * getApp
     * @param  int|null $coroutine_id
     * @return $object
     */
    public function getApp($coroutine_id = null)
    {
        if ($coroutine_id) {
            $cid = $coroutine_id;
        } else {
            $cid = $this->coroutine_id;
        }
        if (!$cid) {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
        }
        if (isset(WST::$app[$cid])) {
            return WST::$app[$cid];
        } else {
            return WST::$app;
        }
    }

    public function destroy($coroutine_id = null)
    {

        if ($coroutine_id) {
            $cid = $coroutine_id;
        } else {
            $cid = $this->coroutine_id;
        }
        if (!$cid) {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
        }
        CoroutineModel::removeInstance($cid);
        self::removeApp();
    }

    /**
     * removeApp
     * @param  int|null $coroutine_id
     * @return boolean
     */
    public static function removeApp($coroutine_id = null)
    {
        $cid = CoroutineManager::getInstance()->getCoroutineId();
        if ($coroutine_id) {
            $cid = $coroutine_id;
        }
        if (isset(self::$app[$cid])) {
            unset(self::$app[$cid]);
            return true;
        } else {
            self::$app = NULL;
        }
        return true;
    }

    public function getFd()
    {
        return $this->fd;
    }

    public static function configure(&$object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }

}