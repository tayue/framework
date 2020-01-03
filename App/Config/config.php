<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/5
 * Time: 13:13
 */
return [
    'id' => 'app',
    'routeRule' => 1, //1:PATHINFO 2:QUERY
    'is_swoole_http_server'=>true,
    'project_namespace' => 'App', //1:模块化组织 2:非模块化组织
    'project_type' => 1, //1:模块化组织 2:非模块化组织
    'default_module' => 'Home', //1:PATHINFO 2:QUERY
    'default_controller' => 'Index',
    'timeZone'=>'PRC',
    'test_mark'=>"cc",
    'default_action' => 'index',
    'current_module' => '',
    'current_controller' => '',
    'current_action' => '',
    'basePath' => dirname(__DIR__),
    'include_files'=>[__DIR__.DIRECTORY_SEPARATOR.'config.php',__DIR__.DIRECTORY_SEPARATOR.'server.php'], //重启工作进程时需要重新载入的配置文件
    'log'=>[
        'log_dir'=>dirname(__DIR__).DIRECTORY_SEPARATOR.'Log',
        'is_display'=>true
    ],
    'components' => [
        'tool' => [
            'class' => 'Framework\Tool\Tool',
            'arr' => [1, 2, 3],
         ],
        'db' => [
            'is_destroy' => 0,//每次请求后是否销毁对象
            'is_delay' => true,//延迟创建实例，请求时候再创建
            'class' => 'Framework\Core\Mysql',
            'config' => [
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
                'debug' => false,
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
            ]
        ],
        'view' => [
            'class' => 'Framework\SwServer\View',
            'config' => [
             ],
        ],
        'eventmanager'=>[
            'class' => 'Framework\SwServer\Event\EventManager',
        ],
        'user' => [
            'class' => 'App\Component\User',
            'arr' => [1, 2, 3],

        ],
    ],
    'services'=>[
        'userService' => [
            'class' => 'App\Service\User',
            'arr' => [1, 2, 3,4],
       ],
        'cryptService' => [
            'class' => 'App\Service\Crypt'
       ],
    ]
];