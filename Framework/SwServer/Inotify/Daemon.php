<?php
 
namespace Framework\SwServer\Inotify;


class Daemon {
	/**
	 * $config 配置
	 * @var array
	 */
	public $config = [
		'afterNSeconds' => 3,
		'isOnline' => false,
		'monitorPort' => 9501,
		'monitorPath' => '/home/wwwroot/default/framework',
		'logFilePath' => '',
		'reloadFileTypes' => ['.php','.html','.js'],
     ];

	/**
	 * __construct 初始化函数
	 * @param    {String}
	 */
	public function __construct(array $config = []) {	
		$this->config = array_merge($this->config, $config['inotify']);
	}

	/**
	 * autoReload文件变动的自动检测与swoole自动重启服务
	 * @param    null
	 * @return   void
	 */
	public function autoReload() {
		$autoReload = new Reload();
		isset($this->config['afterNSeconds']) && $autoReload->afterNSeconds = $this->config['afterNSeconds'];
		isset($this->config['isOnline']) && $autoReload->isOnline = $this->config['isOnline'];
		isset($this->config['monitorPort']) && $autoReload->monitorPort = $this->config['monitorPort'];
		isset($this->config['logFilePath']) && $autoReload->logFilePath = $this->config['logFilePath'];
		isset($this->config['reloadFileTypes']) && $autoReload->reloadFileTypes = $this->config['reloadFileTypes'];
		isset($this->config['smtpTransport']) && $autoReload->smtpTransport = $this->config['smtpTransport'];
		isset($this->config['message']) && $autoReload->message = $this->config['message'];
		// 初始化配置
		$autoReload->init();
		// 开始监听
		$autoReload->watch($this->config['monitorPath']);
		file_put_contents('./watchFiles.txt',var_export($autoReload->getwatchFiles(),true));
	}

	// 启动服务的eventloop
	public function run() {
		$this->autoReload();
		swoole_event_wait();
	}
}