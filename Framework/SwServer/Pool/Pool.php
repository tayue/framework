<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 16:37
 */

namespace Framework\SwServer\Pool;

interface Pool
{
    public function initPool();
    public function get($timeout);
    public function put($data);
    public function getLength();
    public function createResource();
}