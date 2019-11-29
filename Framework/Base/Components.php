<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 11:32
 */

namespace Framework\Base;


abstract class Components extends BaseObject
{
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }


    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        } else {
            $this->$name = $value;
        }

    }

    public function __isset($name)
    {
        return parent::__isset($name);
    }


}