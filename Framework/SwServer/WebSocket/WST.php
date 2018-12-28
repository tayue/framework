<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 11:20
 */

namespace Framework\SwServer\WebSocket;

use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Coroutine\CoroutineModel;

class WST
{
    use \Framework\Traits\ContainerTrait;
    public static $app;

    /**
     * getApp
     * @param  int|null $coroutine_id
     * @return $object
     */
    public static function getApp($coroutine_id = null)
    {
        $cid = CoroutineManager::getInstance()->getCoroutineId();
        if ($coroutine_id) {
            $cid = $coroutine_id;
        }
        if (isset(self::$app[$cid])) {
            return self::$app[$cid];
        } else {
            return self::$app;
        }
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

    public static function destroy()
    {
        CoroutineModel::removeInstance();
        self::removeApp();
    }


}