<?php
namespace Ypf;

class Swoole extends Ypf {

	const VERSION = '0.0.2';
	const LISTEN = '127.0.0.1:9002';

    private $serverConfig;

	private $server;
		
	public function setServerConfigIni($serverConfigIni) {
        if (!is_file($serverConfigIni)) {
            trigger_error('Server Config File Not Exist!', E_USER_ERROR);
        }
        $serverConfig = parse_ini_file($serverConfigIni, true);
        if (empty($serverConfig)) {
            trigger_error('Server Config Content Empty!', E_USER_ERROR);
        }
        $this->serverConfig = $serverConfig;
    }
	
	public function start() {
		$listen = isset($this->serverConfig["server"]["listen"]) ?
		$this->serverConfig["server"]["listen"] : self::LISTEN;
		list($addr, $port) = explode(":", $listen, 2);
		$this->server = new \swoole_http_server($addr, $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
		isset($this->serverConfig['swoole']) && $this->server->set($this->serverConfig['swoole']);
		$this->server->on('Task', "\Ypf\Swoole\Task::task");
		$this->server->on('Finish', "\Ypf\Swoole\Task::finish");
        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('WorkerStop', array($this, 'onWorkerStop'));
        $this->server->on('Request', array($this, 'onRequest'));
        $this->server->on('ShutDown', array($this, 'onShutDown'));
        $this->server->start();	
	}
	
    public function onStart(\swoole_http_server $server) {
    	$name = isset($this->serverConfig['server']['master_process_name']) ? 
    	$this->serverConfig['server']['master_process_name'] : 'ypf:swoole-master';
        \swoole_set_process_name($name);
        return true;
    }

    public function onManagerStart(\swoole_http_server $server) {
    	$name = isset($this->serverConfig['server']['manager_process_name']) ? 
    	$this->serverConfig['server']['manager_process_name'] : 'ypf:swoole-manager';
        \swoole_set_process_name($name);
        return true;
    }

    public function onWorkerStart(\swoole_http_server $server, $worker_id) {
		if($worker_id >= $server->setting['worker_num']) {
			$name = isset($this->serverConfig['server']['task_worker_process_name']) ? 
			$this->serverConfig['server']['task_worker_process_name'] : 'ypf:swoole-task-worker-%d';
			$processName = sprintf($name, $worker_id);
		}else{
			$name = isset($this->serverConfig['server']['worker_process_name']) ? 
			$this->serverConfig['server']['worker_process_name'] : 'ypf:swoole-worker-%d';
			$processName = sprintf($name, $worker_id);
		}
        \swoole_set_process_name($processName);
        return true;
    }

    public function onWorkerStop(\swoole_http_server $server, $workerId) {
        return true;
    }

    public function onShutDown(\swoole_http_server $server) {
        return true;
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response) {
        $this->request->init($request);
        $this->response->init($response);
        $this->disPatch();
		$this->response->output();
        //$response->end("<h1>Hello Swoole</h1>");
    }
    
}