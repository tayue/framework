<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 10:26
 */

namespace Framework\SwServer\Protocol;
use Framework\SwServer\BaseServer;
use Framework\Tool\Log;
use Framework\SwServer\ServerApplication;
use Framework\SwServer\ServerManager;
use Framework\SwServer\DataPackage\Pack;


class TcpServer extends BaseServer
{

    public $fd;
    /**
     * $pack 封解包对象
     * @var null
     */
    public $pack = null;

    public function __construct($config)
    {
        parent::__construct($config);
        self::$isWebServer = false;
        $this->setSwooleSockType();
    }

    public function createServer()
    {
        self::$server = new \swoole_server($this->config['server']['listen_address'], $this->config['server']['listen_port'], self::$swoole_process_mode, self::$swoole_socket_type);
        self::$server->set($this->setting);
        Log::getInstance()->setConfig($this->config);
        $this->setLogger(Log::getInstance());
        // 设置Pack包处理对象
        $this->buildPackObj();
        return self::$server;
    }

    public function getServer()
    {
        return self::$server;
    }

    public function onMasterStart()
    {

    }

    function onStart($server)
    {
        echo "TcpServer onStart\r\n";
    }

    function onWorkerStart($server, $worker_id)
    {
        //初始化应用层
        $app = new ServerApplication($this->config);
        ServerManager::$serverApp = \serialize($app);
    }

    function onConnect($server, $client_id, $from_id)
    {

    }

    function onReceive($server, $fd, $reactor_id, $data)
    {
        try {
            // 服务端为length检查包
            if (ServerManager::isPackLength()) {
                $recv = $this->pack->depack($server, $fd, $reactor_id, $data);
            } else {
                // 服务端为eof检查包
                $recv = $this->pack->depackeof($data);
            }
            if ($recv) {
                $serverApp = \unserialize(ServerManager::$serverApp);
                $serverApp->tcpRun($fd, $recv);
            }
            ServerManager::destroy(); //销毁应用实例
            return;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    function onClose($server, $client_id, $from_id)
    {
        // TODO: Implement onClose() method.
    }

    function onShutdown($server)
    {

    }


    function onFinish(\swoole_server $serv, $task_id, $data)
    {
        echo "Task#$task_id finished, info=" . $data . PHP_EOL;
    }

    /**
     * buildPackHander 创建pack处理对象
     * @return void
     */
    public function buildPackObj()
    {
        $this->pack = new Pack(self::$server);
        if (ServerManager::isPackLength()) {
            // packet_length_check
            $this->pack->header_struct = $this->config['packet']['server']['pack_header_struct'];
            $this->pack->pack_length_key = $this->config['packet']['server']['pack_length_key'];
            if (isset($this->config['packet']['server']['serialize_type'])) {
                $this->pack->serialize_type = $this->config['packet']['server']['serialize_type'];
            }
            $this->pack->header_length = $this->config['server']['setting']['package_body_offset'];
            $this->pack->packet_maxlen = $this->config['server']['setting']['package_max_length'];
        } else {
            // packet_eof_check
            $this->pack->pack_eof = $this->config['server']['setting']['package_eof'];
            $this->pack->serialize_type = Pack::DECODE_JSON;
        }
    }

    /**
     * isClientPackEof 根据设置判断客户端的分包方式
     * @return boolean
     */
    public static function isClientPackEof()
    {
        if (isset(ServerManager::$config['packet']['client']['pack_check_type'])) {
            if (ServerManager::$config['packet']['client']['pack_check_type'] == 'eof') {
                //$client_check是eof方式
                return true;
            }
            return false;
        } else {
            throw new \Exception("you must set ['packet']['client']  in the config file", 1);
        }

    }

    /**
     * pack  根据配置设置，按照客户端的接受数据方式，打包数据发回给客户端
     * @param    mixed $data
     * @param    int $fd
     * @return   void
     */
    public static function pack($data)
    {
        if (static::isClientPackEof()) {
            list($data) = $data;
            $eof = ServerManager::$config['packet']['client']['pack_eof'];
            $serialize_type = ServerManager::$config['packet']['client']['serialize_type'];
            if ($eof) {
                $pack_data = Pack::enpackeof($data, $serialize_type, $eof);
            } else {
                $pack_data = Pack::enpackeof($data, $serialize_type);
            }
            return $pack_data;

        } else {
            // 客户端是length方式分包
            list($body_data, $header) = $data;
            $header_struct = ServerManager::$config['packet']['client']['pack_header_struct'];
            $pack_length_key = ServerManager::$config['packet']['client']['pack_length_key'];
            $serialize_type = ServerManager::$config['packet']['client']['serialize_type'];
            $header[$pack_length_key] = '';
            $pack_data = Pack::enpack($body_data, $header, $header_struct, $pack_length_key, $serialize_type);
            return $pack_data;
        }
    }


}