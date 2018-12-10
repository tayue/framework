<?php


namespace Framework\SwServer\Crontab;

abstract class AbstractCronTask
{
    abstract public static function getRule();
    abstract public static function getTaskName();

}