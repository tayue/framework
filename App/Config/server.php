<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 13:13
 */
return [
    'open_table_tick_task' => true,
    'server' => [
        'pid_file' => ROOT_PATH . '/Data/pid.pid',
        'server_type' => 'WEB_SOCKET_SERVER',
        'listen_address' => '192.168.99.88',
        'listen_port' => 9501,
        'www_user' => 'root',
        'setting' => [
            'reactor_num' => 1,
            'worker_num' => 4,
            'max_request' => 10000,
            'task_worker_num' => 4,
            'task_tmpdir' => '/dev/shm',
            'daemonize' => 0,
            // TCP使用固定的worker，使用2或4或7
            'dispatch_mode' => 2,
            // 'open_eof_check' => true, //打开EOF检测
            // 'open_eof_split' => true, //打开EOF_SPLIT检测
            // 'package_eof' => "\r\n\r\n", //设置EOF
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset' => 34,       //第几个字节开始计算长度
            'package_max_length' => 2000000,  //协议最大长度
        ],
    ],
    'packet' => [
        // 服务端使用长度检查packet时，设置包头结构体，如果使用eof时，不需要设置，底层会读取package_eof
        'server' => [
            'pack_header_struct' => ['length' => 'N', 'name' => 'a30'],
            'pack_length_key' => 'length',
            'serialize_type' => 'json'
        ],
        // 若客户端的分包设置，eof分包
        'client' => [
            'pack_check_type' => 'length',
            'pack_header_struct' => ['length' => 'N', 'name' => 'a30'],
            'pack_length_key' => 'length',
            'serialize_type' => 'json'
        ]
        // 若客户端length检查设置，则设置这个length配置
        // client => [
        // 'pack_check_type' => 'length',
        // 'pack_header_strct' => ['length'=>'N','name'=>'a30'],
        // 'pack_length_key' => 'length',
        // 'serialize_type' => 'json'
        // ]
    ],
    'mysql_pool' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => 'localhost',
        // 数据库名
        'database' => 'test',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => 'root',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        // 'dsn'             => '',
        // 数据库连接参数
        // 'params'          => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        // 'prefix'          => '',
        // 数据库调试模式
        'debug' => true,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        // 'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        // 'rw_separate'     => false,
        // 读写分离后 主服务器数量
        // 'master_num'      => 1,
        // 指定从服务器序号
        // 'slave_no'        => '',
        // 是否严格检查字段是否存在
        // 'fields_strict'   => true,
        // 数据集返回类型
        'resultset_type' => 'collection',
        // 自动写入时间戳字段
        // 'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        // 'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        // 'sql_explain'     => false,
        // Builder类
        // 'builder'         => '',
        // Query类
        'query' => '\\Framework\Core\\db\\Query',
        // 是否需要断线重连
        'break_reconnect' => true,
        'timeout' => 0.5,       //数据库连接超时时间
        'charset' => 'utf8', //默认字符集
        'strict_type' => true,  //ture，会自动表数字转为int类型
        'space_time' => 10 * 3600,
        'mix_pool_size' => 300,     //最小连接池大小
        'max_pool_size' => 1000,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ],
    'coro_mysql_pool' => [
        'host' => '192.168.99.88',   //数据库ip
        'port' => 3306,          //数据库端口
        'user' => 'root',        //数据库用户名
        'username' => 'root',        //数据库用户名
        'password' => 'root', //数据库密码
        'database' => 'test',   //默认数据库名
        'timeout' => 0.5,       //数据库连接超时时间
        'charset' => 'utf8', //默认字符集
        'strict_type' => true,  //ture，会自动表数字转为int类型
        'space_time' => 10 * 3600,
        'mix_pool_size' => 4,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ],
    'redis_pool' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'timeout' => 0.5,       //数据库连接超时时间
        'space_time' => 10 * 3600,
        'mix_pool_size' => 4,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ],
    'inotify' => [
        'afterNSeconds' => 3,
        'isOnline' => false,
        'monitorPort' => 9501,
        'monitorPath' => '/home/wwwroot/default/framework',
        'logFilePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Log' . DIRECTORY_SEPARATOR . 'inotify.log',
        'monitorProcessName' => 'php-inotify-swoole-server',
        'reloadFileTypes' => ['.php', '.html', '.js'],
    ]

];
