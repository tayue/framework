<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 17:48
 */

namespace Framework\SwServer;


class BaseServerManager
{
    public $process_name = '';
    public static $pidFile;
    /**
     * 设置进程的名称
     * @param $name
     */
    public function setProcessName($name)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                @swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__ . " failed. require cli_set_process_title or swoole_set_process_name.");
            }
        }
        $this->process_name=$name;
    }

    public function getProcessName(){
        return $this->process_name;
    }
}