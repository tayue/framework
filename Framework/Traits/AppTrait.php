<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

use Framework\SwServer\Coroutine\CoroutineModel;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\ServerManager;


trait AppTrait
{
    public static $app;


    /**
     * getApp
     * @param  int|null $coroutine_id
     * @return $object
     */
    public static function getApp($coroutine_id = null)
    {
        if ($coroutine_id) {
            $cid = $coroutine_id;
        } else { 
            $cid = ServerManager::getInstance()->coroutine_id;
        }
        if (!$cid) {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
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

    public static function destroy($coroutine_id = null)
    {

        if ($coroutine_id) {
            $cid = $coroutine_id;
        } else {
            $cid = ServerManager::getInstance()->coroutine_id;
        }
        if (!$cid) {
            $cid = CoroutineManager::getInstance()->getCoroutineId();
        }
        CoroutineModel::removeInstance($cid);
        self::removeApp();
    }

//    public function __set($name, $value)
//    {
//        $currentApp = self::getApp();
//        $currentApp->$name = $value;
//    }
}