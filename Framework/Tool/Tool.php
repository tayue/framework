<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 13:33
 */

namespace Framework\Tool;


use Framework\Base\Components;

class Tool extends Components
{
    public $name='';
    public $arr=array();

    public function display(){

    }

    public function setName($value=''){
        $this->name=$value;
    }

    public function getName(){
        $this->name;
    }
}