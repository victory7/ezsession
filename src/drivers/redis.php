<?php
namespace EzSession;

require_once __DIR__ . '/../functions.php';

use Predis\Client;

class Redis {
    private $host   = '127.0.0.1';
    private $port   = 6379;
    private $scheme = 'tcp';
    private $pass   = null;
    private $conn   = null;
    private $prekeyname  = 'session_';
    private $cacheTime  = 60;
    

    public function __construct(array $config = []) {
        if (!empty($config['scheme'])) {
            $this->scheme = $config['scheme'];
        }

        if (!empty($config['host'])) {
            $this->host   = $config['host'];
        }

        if (!empty($config['port'])) {
            $this->port   = $config['port'];
        }

        if (!empty($config['pass'])) {
            $this->pass   = $config['pass'];
        }

        if (!empty($config['cacheTime'])) {
            $this->cacheTime = $config['cacheTime'];
        }

        // Create a new Predis client instance
        $this->conn = new Client([
            'scheme' => $this->scheme,
            'host'   => $this->host,
            'port'   => $this->port,
        ]);
    }

    public function __destruct() {
        $this->conn = null;
    }

    // Check if a key exists
    public function exists($key, array $options = []) {
        $key = $this->prekeyname . $key;
        return $this->conn->exists($key);
    }

    // Get the value back
    public function get($rawKey, array $options = []) {   
        $key = $this->prekeyname . $rawKey;
        $res = $this->conn->get($key);

        if ($res !== null) {
            $this->set_ttl($key, $this->cacheTime);
        }
        return $res;
    }

    // Get ttl
    public function get_ttl($key) {
        $key = $this->prekeyname . $key;
        $res = $this->conn->ttl($key);

        $res = $res;

        return $res;
    }

    // Set ttl
    public function set_ttl($key, int $exSeconds = 60, $options = []) {    
        $key = $this->prekeyname . $key;
        $res = $this->conn->expire($key, $exSeconds);

        return $res;
    }

    // Set a value in Redis
    public function set(string $key, $value, array $options = []) {
        $key = $this->prekeyname . $key;

        $this->conn->set($key, $value);
    }

    // Set a value with an expiration time  
    public function setex(string $key, $value, int $exSeconds = 60, array $options = []) {
        $key = $this->prekeyname . $key;
        $this->conn->setex($key, $exSeconds, $value);
    }

    public function update(array $data = []) {
        
    }

    public function delete($key, array $options = []) {
        $key = $this->prekeyname . $key;
        $this->conn->del($key);
    }
}