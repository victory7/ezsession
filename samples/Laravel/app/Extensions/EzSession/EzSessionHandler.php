<?php
namespace App\Extensions\EzSession;

use EzSession\Config;
use EzSession\SessionHandler;
use SessionHandlerInterface;

class EzSessionHandler implements SessionHandlerInterface {
	private $token  = '';
	private $config  = [];
	private $handler = [];

	public function __construct($data = []) {
		
		$this->config  = new Config($data);
        $this->handler = new SessionHandler($this->config);

		$res = $this->handler->init($this);

        $this->token = $res['token'] ?? '';

		session_set_save_handler($this, true);
	}

    public function open($savePath, $sessionName)
    {
        return true;
    }	

    public function close()
    {
        return true;
    }

    public function read($token)
    {
		$data = $this->handler->read(['token' => $token]);

		return $data;
    }

    public function write($token, $data)
    {
		$this->handler->write(['token' => $token, 'data' => $data]);

		return true;
    }

    public function destroy($token)
    {
		$this->handler->destroy(['token' => $token]);

        return true;
    }

    public function gc($maxLifeTime)
    {
		$this->handler->gc(['maxLifeTime' => $maxLifeTime]);

        return true;
    }
}