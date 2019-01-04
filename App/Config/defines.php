<?php
define('APP_NAME','App');
define('APP_PATH',dirname(__DIR__));
define('ROOT_PATH',dirname(APP_PATH));
define('DATA_PATH',ROOT_PATH.'/Data');
define('CONFIG_PATH',APP_PATH.'/Config');
define('VENDOR_PATH',ROOT_PATH.'/vendor');

// 定义服务协议常量
defined('SWOOLE_HTTP') or define('SWOOLE_HTTP', 'http');
defined('SWOOLE_WEBSOCKET') or define('SWOOLE_WEBSOCKET', 'websocket');
defined('SWOOLE_TCP') or define('SWOOLE_TCP', 'tcp');
defined('SWOOLE_UDP') or define('SWOOLE_UDP', 'udp');

// 定义打包检查类型
defined('SWOOLE_PACK_CHECK_LENGTH') or define('SWOOLE_PACK_CHECK_LENGTH', 'length');
defined('SWOOLE_PACK_CHECK_EOF') or define('SWOOLE_PACK_CHECK_EOF', 'eof');

// 日志目录
define('LOG_PATH',APP_PATH.'/Log');

// 定义smarty
define('SMARTY_TEMPLATE_PATH',APP_PATH.'/View/');
define('SMARTY_COMPILE_DIR',APP_PATH.'/Runtime/');
define('SMARTY_CACHE_DIR',APP_PATH.'/Runtime/');