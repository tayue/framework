<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/3
 * Time: 16:52
 */

namespace Framework\Core\error;

use Framework\Core\Exception;
use Framework\Core\log\Log;

class CustomerError
{
    public static function fatalError()
    {
        $error = error_get_last();
        if ($error) {
            $message = $error['message'];
            $file = $error['file'];
            $line = $error['line'];
            $outMessage = "file:{$file},line {$line} throw error:{$message}\r\n";
            if (isset($error['type'])) {
                switch ($error['type']) {
                    case E_ERROR :
                    case E_PARSE :
                    case E_CORE_ERROR :
                    case E_USER_ERROR:
                        Log::getInstance()->put($outMessage, Log::ERROR);
                        break;
                    case E_COMPILE_ERROR :
                        $message = $error['message'];
                        $file = $error['file'];
                        $line = $error['line'];
                        $log = "$message ($file:$line)\nStack trace:\n";
                        $trace = debug_backtrace();
                        foreach ($trace as $i => $t) {
                            if (!isset($t['file'])) {
                                $t['file'] = 'unknown';
                            }
                            if (!isset($t['line'])) {
                                $t['line'] = 0;
                            }
                            if (!isset($t['function'])) {
                                $t['function'] = 'unknown';
                            }
                            $log .= "#$i {$t['file']}({$t['line']}): ";
                            if (isset($t['object']) and is_object($t['object'])) {
                                $log .= get_class($t['object']) . '->';
                            }
                            $log .= "{$t['function']}()\n";
                        }
                        if (isset($_SERVER['REQUEST_URI'])) {
                            $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                        }
                        Log::getInstance()->put($log, Log::ERROR);
                        break;
                }
            }
        }

    }


    public static function generalError($errorCode, $description, $file = null, $line = null)
    {
        $outMessage = "file:{$file},line {$line} throw error:{$description}\r\n";
        Log::getInstance()->put($outMessage, Log::TRACE);
    }

    public static function writeErrorLog(\Throwable $e)
    {
        $outMessage = "file:{$e->getFile()},line {$e->getLine()} throw error:{$e->getMessage()}\r\n";
        Log::getInstance()->put($outMessage, Log::ERROR);
    }

}