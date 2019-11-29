<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

trait SingletonTrait
{

    private static $instance;

    static function getInstance($args = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($args);
        }
        return self::$instance;
    }
}