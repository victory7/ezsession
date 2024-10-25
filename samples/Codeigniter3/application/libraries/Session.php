<?php
use EzSession\Config;
use EzSession\SessionHandler;

class Session_Ezsession_driver implements SessionHandlerInterface {
	private $config  = [];
	private $handler = [];

	public function __construct($data = []) {
		
		$ci =& get_instance();
        $ci->config->load('Ezsession');
        $config = $ci->config->item('Ezsession');

        if (!empty($config) && is_array($config)) {
			foreach ($config as $key => $value) {
				$data[$key] = $value;
			}
        }
        
		$this->config  = new Config($data);
        $this->handler = new SessionHandler($this->config);

		$this->handler->init($this);

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

    public function gc($maxLifetime)
    {
        $this->handler->gc(['maxLifetime' => $maxLifetime]);

        return true;
    }
}