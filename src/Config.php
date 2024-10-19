<?php
namespace EzSession;

class Config {
    private $settings = [];

    public function __construct(array $config = []) {
        $default = $this->getDefaultConfig();

        foreach ($default as $key => $item) {
            if (isset($config[$key])) {
                $default[$key] = array_merge($item, $config[$key]);
            }
        }

        $this->settings = $default;
    }

    private function getDefaultConfig() {
        return [
            'mysql' => [
                'host' => 'localhost',
                'port' => '3306',
                'user' => 'root',
                'password' => '',
                'database' => 'ezsession',
                'table'    => 'users_sessions',
            ],
            'redis' => [
                'host' => 'localhost',
                'port' => '6379',
                'auth' => null,
                'cacheTime' => 60
            ],
            'jwt' => [
                'secret' => 'your-secret-key',
                'alg' => 'HS256',
            ],            
            'cookie' => [
                'name' => 'SESSTOKEN',
                'path' => '/',
                'domain' => null,
                'secure' => true,
                'httponly' => true,
                'expires' => 3600
            ]
        ];
    }

    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
}
