<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 16:07
 */

namespace Framework\Core\log;

use Framework\Core\Exception;
use Framework\Tool\Tool;
use Framework\Traits\SingletonTrait;

class Log
{
    use SingletonTrait;
    public const INFO = 0;
    public const NOTICE = 1;
    public const TRACE = 2;
    public const ERROR = 3;
    public $level = self::INFO;

    public $levelInfo = [
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::TRACE => 'TRACE',
        self::ERROR => 'ERROR',
    ];


    public $config = [
        'is_display' => false,
        'level' => self::INFO,
        'log_file' => 'Log.log'
    ];

    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function setLevel($level)
    {
        if (isset($level) && in_array($level, array_keys($this->levelInfo))) {
            $this->level = $level;
        }

    }

    function put($msg, $level = self::INFO)
    {
        try {
            if ($this->config['is_display']) {
                echo $this->format($msg, $level);
            } else { //写入到日志里面
                $log_dir = $this->config['log_dir']; //日志目录
                if (!$log_dir) {
                    throw new Exception("请设置日志目录!");
                }
                if (!is_dir($log_dir)) {
                    $res = Tool::createDir($log_dir);
                    if (!$res) {
                        throw new Exception("目录{$log_dir}创建失败!!");
                    }
                }
                $logFilePath = $log_dir . DIRECTORY_SEPARATOR . date("Y_m_d_") . $this->config['log_file'];
                $msg = $this->format($msg, $level);
                $msg = $msg . "\r\n";
                file_put_contents($logFilePath, $msg, FILE_APPEND);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    public function format($msg, $level = '')
    {
        if (!$level) {
            $level = $this->level;
        }
        $dateFormatStr = "[Y-m-d H:i:s]";
        return date($dateFormatStr) . "\t{$this->levelInfo[$level]}\t{$msg}";

    }
}
