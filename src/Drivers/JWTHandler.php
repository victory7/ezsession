<?php

namespace EzSession\Drivers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTime;
use Exception;

class JWTHandler {
    private $secret = '';
    private $algorithm = 'HS256';

    public function __construct(array $config = []) {
        $this->secret = $config['secret'];
    }

    public function __destruct() {
        
    }

    function set(array $data = []) {
        $JWT = new JWT();
        $KEY = new Key($this->secret, $this->algorithm);

        $now = new DateTime('now');
        $expire = (clone $now)->modify('+365 day');

        $payload = [
            "iss" => 'issData',
            "aud" => 'audData',
            "iat" => $now->getTimestamp(),
            "exp" => $expire->getTimestamp()
        ];

        $payload = array_merge($data, $payload);

        return $JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function get(String $token) {
        $JWT = new JWT();
        $KEY = new Key($this->secret, $this->algorithm);

        try {
            return (array) $JWT::decode($token, $KEY);
        } catch (Exception $e) {
            return false;
        }
    }

}