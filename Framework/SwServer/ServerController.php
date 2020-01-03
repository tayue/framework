<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2019/1/2
 * Time: 9:44
 */

namespace Framework\SwServer;
use Framework\SwServer\Base\BaseObject;

class ServerController extends BaseObject
{
    public function init(){

    }
    public function __call($name, $arguments='')
    {
        // TODO: Implement __call() method.
        echo __CLASS__."方法{$name}不存在!!";
    }
}