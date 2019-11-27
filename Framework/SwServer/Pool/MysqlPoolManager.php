<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 15:13
 */

namespace Framework\SwServer\Pool;
use Framework\Traits\SingletonTrait;
use Framework\Core\Mysql;
use Framework\SwServer\ServerManager;

class MysqlPoolManager extends PoolBase
{
    use SingletonTrait;
    public $poolObject = '';
    public $default_config = [
        'host' => '192.168.99.88',   //数据库ip
        'port' => 3306,          //数据库端口
        'user' => 'root',        //数据库用户名
        'password' => 'root', //数据库密码
        'database' => 'test',   //默认数据库名
        'timeout' => 0.5,       //数据库连接超时时间
        'charset' => 'utf8mb4', //默认字符集
        'strict_type' => true,  //ture，会自动表数字转为int类型
        'space_time'=>100,       //100s间隔
        'mix_pool_size' => 2,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ];

    public function checkConnection()
    {
        try {
            $mysql = new Mysql($this->config);
            if(!ServerManager::$isEnableRuntimeCoroutine){ //没有开启运行时协程那么自动切换到协程mysql客户端
                $mysql =$mysql->selectMysql();
            }
            if (!$mysql) {
                //连接失败，抛弃常
                throw new \Exception("failed to connect mysql server.");
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
            return false;
        }
        return $mysql;
    }

    public function __construct($config = [])
    {
        if ($config) {
            $this->config = array_merge($this->default_config, $config);
        } else {
            $this->config = $this->default_config;
        }
        parent::__construct($this->config);
    }



}