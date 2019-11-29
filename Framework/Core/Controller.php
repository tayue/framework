<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/12
 * Time: 16:51
 */

namespace Framework\Core;

class Controller extends View
{
    public function init(){

    }
    public function __call($name, $arguments='')
    {
        // TODO: Implement __call() method.
        echo __CLASS__."方法{$name}不存在!!";
    }

}