<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 16:07
 */

namespace Framework\Tool;
use Framework\Traits\SingletonTrait;
use Exception;

class Log
{
    use SingletonTrait;
    const INFO = 0;
    const NOTICE = 1;
    const TRACE = 2;
    const ERROR = 3;
    public $level = self::INFO;

    static $levelInfo = [
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
        if (isset($level) && in_array($level, array_keys(self::$levelInfo))) {
            $this->level = $level;
        }

    }

    public static function Conversion($level = '')
    {
        if (!$level) {
            $level = self::INFO;
            return self::$levelInfo[$level];
        }
        $level = strtoupper($level);
        if (in_array($level, self::$levelInfo)) {
            $tmp = array_flip(self::$levelInfo);
            return $tmp[$level];
        }
        $level = self::INFO;
        return self::$levelInfo[$level];

    }

    function put($msg, $level = '')
    {
        $level = self::Conversion($level);
        if ($this->config['is_display']) {
            echo $this->format($msg, $level);
        } else { //写入到日志里面
            $log_dir = $this->config['log_dir']; //日志目录
            if (!is_dir($log_dir)) {
                $is_dir = Tool::createDir($log_dir);
                if (!$is_dir) {
                    throw new Exception("目录{$log_dir}创建失败!!");
                }
            }
            if (!$msg) {
                return;
            }
            $logFilePath = $log_dir . DIRECTORY_SEPARATOR . date("Y_m_d_") . $this->config['log_file'];
            $msg = $this->format($msg, $level);
            $msg = $msg . "\r\n";
            file_put_contents($logFilePath, $msg, FILE_APPEND);
        }
    }

    public function format($msg, $level = '')
    {
        if (!$level) {
            $level = $this->level;
        }
        $dateFormatStr = "[Y-m-d H:i:s]";
        return date($dateFormatStr) . "\t" . self::$levelInfo[$level] . "\t" . $msg;
    }
}
