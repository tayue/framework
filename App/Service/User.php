<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/14
 * Time: 13:40
 */
namespace App\Service;
use Framework\Framework;
class User
{
    public $arr=[];
    public function display(){
        $a=Framework::getApp()->db->table('user')->find();
        var_dump($a);

    }

}