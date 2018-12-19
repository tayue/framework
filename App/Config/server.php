<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 13:13
 */
return [
    'open_table_tick_task' => true,
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

];