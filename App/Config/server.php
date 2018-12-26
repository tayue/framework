<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 13:13
 */
return [
    'open_table_tick_task' => true,
    'server'=>[
        'pid_file'=>ROOT_PATH.'/Data/pid.pid',
        'server_type'=>'WEB_SOCKET_SERVER',
        'listen_address'=>'192.168.99.88',
        'listen_port'=>9501,
        'www_user'=>'root',
        'setting'=>[
            'reactor_num' => 1,
            'worker_num' => 4,
            'max_request' => 1000,
            'task_worker_num' => 4,
            'task_tmpdir' => '/dev/shm',
            'daemonize' => 0
        ]
    ],
    'mysql_pool' => [
        'host' => '192.168.99.88',   //数据库ip
        'port' => 3306,          //数据库端口
        'user' => 'root',        //数据库用户名
        'password' => 'root', //数据库密码
        'database' => 'test',   //默认数据库名
        'timeout' => 0.5,       //数据库连接超时时间
        'charset' => 'utf8mb4', //默认字符集
        'strict_type' => true,  //ture，会自动表数字转为int类型
        'space_time' => 10 * 3600,
        'mix_pool_size' => 2,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ],
    'inotify'=>[
        'afterNSeconds' => 3,
        'isOnline' => false,
        'monitorPort' => 9501,
        'monitorPath' => '/home/wwwroot/default/framework',
        'logFilePath' => dirname(__DIR__).DIRECTORY_SEPARATOR.'Log'.DIRECTORY_SEPARATOR.'inotify.log',
        'monitorProcessName' => 'php-inotify-swoole-server',
        'reloadFileTypes' => ['.php','.html','.js'],
    ]

];