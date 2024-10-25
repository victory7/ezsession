<?php
namespace EzSession;

use EzSession\Drivers\Mysql;
use EzSession\Drivers\Redis;
use EzSession\Drivers\JWTHandler;
use EzSession\Config;

use Ramsey\Uuid\Uuid;

class SessionHandler {
    private $config;
    private $tokenData      = [];
    private $dirtyTokenData = [];
    private $data           = '';

    public function __construct(Config $config) {
        $this->config = $config;
        $this->gc(['maxLifeTime' => 7200]);
    }

    public function init()
	{
		ini_set('session.serialize_handler', 'php_serialize');

		$token   = $this->getTOKEN();
		$jwtData = $this->reviewToken($token);

		$cookieConfig = $this->config->get('cookie');

		session_set_cookie_params([
		    'lifetime' => 0,
		    'path'     => $cookieConfig['path'],
		    'domain'   => $cookieConfig['domain'],  // Can be set to a specific domain
		    'secure'   => $cookieConfig['secure'],  // Only send cookie over HTTPS if true
		    'httponly' => $cookieConfig['httponly'] // Only accessible through HTTP, not JavaScript
		]);

		session_name($cookieConfig['name']);
		session_id($token);

		return ['jwtData' => $jwtData];
	}

    public function getTOKEN() {
        // echo "get token<br>";
        $jwt  = $this->get_token();

        if (empty($jwt)) {
            $jwt = $this->set_jwt(['stored' => false]);

            // echo "<br>new token<br>";
        }

        return $jwt;
    }

    public function reviewToken($token = '') {
        $tokenData = $this->get_jwt($token);

        $this->tokenData = $tokenData;

        return $tokenData;
    }

    private function addJWT($data)
	{
		$dataDecoded = unserialize($data);
		$dataDecoded['jwt'] = $this->tokenData;
		$data = serialize($dataDecoded);

		return $data;
	}

    private function renderJWT($data)
	{
		$dataDecoded = unserialize($data);

		$changed = json_encode($dataDecoded['jwt']) != json_encode($this->tokenData);

		if ($changed) {
			$dataDecoded['save->jwt'] = $dataDecoded['jwt'];
		}

    	unset($dataDecoded['jwt']);
    	$data = serialize($dataDecoded);

    	if ($data === 'a:0:{}') {
    		$data = '';
    	}

    	return $data;
	}

    public function updateSessionCookie($value = '')
	{
		$cookieConfig = $this->config->get('cookie');

		setcookie($cookieConfig['name'], $value, [
		    'expires'  => time() + 3600, // Expires in 1 hour
		    'path'     => $cookieConfig['path'],
		    'domain'   => $cookieConfig['domain'],  // Can be set to a specific domain
		    'secure'   => $cookieConfig['secure'],  // Only send cookie over HTTPS if true
		    'httponly' => $cookieConfig['httponly'], // Only accessible through HTTP, not JavaScript
		    'samesite' => 'None'
		]);
	}

	public function deleteSessionCookie()
	{
		$cookieConfig = $this->config->get('cookie');
		
		setcookie($cookieConfig['name'],'', [
		    'expires'  => time() - 3600, // Expires in 1 hour
		    'path'     => $cookieConfig['path'],
		    'domain'   => $cookieConfig['domain'],  // Can be set to a specific domain
		    'secure'   => $cookieConfig['secure'],  // Only send cookie over HTTPS if true
		    'httponly' => $cookieConfig['httponly'], // Only accessible through HTTP, not JavaScript
		    'samesite' => 'None'
		]);
	}

    public function read($data = []) {
        if (empty($this->tokenData)) {
            $this->reviewToken($data['token']);
        }

        // if it's a raw jwt (not any stored data)
        if (empty($this->tokenData['stored'])) {
    		return serialize(['jwt' => $this->tokenData]);
    	}

        $resData = $this->get_from_cache() ?? '';
        
        if (empty($resData)) {
            $resData = $this->get_from_db();
            
            if (!empty($resData)) {
                $saveDataToCache = ['data' => $resData];
                $this->save_to_cache($saveDataToCache);
            }
        }

        if (empty($resData)) {
            $resData = "";
        }

        $this->data = $resData;

        $resData = $this->addJWT($resData);
        
        return $resData;
    }

    public function write($data = []) {
        $tokenData = $this->reviewToken($data['token']);
        $dataToSave = $data['data'];

        $dataToSave = $this->renderJWT($dataToSave);
        
        if (empty($tokenData)) {
            return true;
        }
        
        // No need to save if data is same
        if (empty($this->tokenData['stored']) && $dataToSave == $this->data) {
            // echo "no need to save";
            return true;
        }
        
        $dataToSave = $this->dateManipulate_saveToJWT($dataToSave);
        
        if ($this->data !== $dataToSave) {
            $this->data = $dataToSave;

            $this->save_to_cache(['data' => $dataToSave]);
            $this->save_to_db(['data' => $dataToSave]);

            $this->handleStoredFlagAfterWrite();
        }
        
        $this->handleDirtyToken();

        return true;
    }

    private function handleStoredFlagAfterWrite() {
        if ($this->tokenData['stored'] === false) {
            $this->dirtyTokenData = array_merge($this->tokenData, ['stored' => true]);
        } elseif ($this->tokenData['stored'] === true && empty($this->data)) {
            $this->dirtyTokenData = array_merge($this->tokenData, ['stored' => false]);    
        }
    }

    private function handleDirtyToken() {
        // Create new token - if tokenData is changed
        if (!empty($this->dirtyTokenData)) {
            $newToken = $this->set_jwt($this->dirtyTokenData);
            $this->updateSessionCookie($newToken);

            $this->tokenData = $this->dirtyTokenData;
            $this->dirtyTokenData = [];

            if ($this->tokenData['stored'] === false && empty($this->data)) {
                $this->clearDB();
            }
        }
    }

    function dateManipulate_saveToJWT($data) {
        $dataToSaveDecoded = unserialize($data);

        // Save To JWT
        if (!empty($dataToSaveDecoded['save->jwt'])) {
            // echo "here";
            $this->dirtyTokenData = $dataToSaveDecoded['save->jwt'];

            unset($dataToSaveDecoded['save->jwt']);
            if (!empty($dataToSaveDecoded)) {
                $data = serialize($dataToSaveDecoded);
            } else {
                $data = '';
            }
        }

        return $data;
    }

    public function destroy($data = []) {
        if (empty($this->tokenData)) {
            $this->reviewToken($data['token']);
        }

        $this->clearDB($data);

        $this->deleteSessionCookie();

        return true;
    }

    public function clearDB($data = []) {
        if (empty($this->tokenData)) {
            $this->reviewToken($data['token']);
        }

        $this->delete_from_cache();
        $this->delete_from_db();

        return true;
    }

    public function gc($data = []) {
        $this->delete_olds_from_db($data);

        return true;
    }

    public function get_jwt(String $token = '') {
        $jwtConfig = $this->config->get('jwt');

        if (empty($token)) {
            $token = $this->get_token();
        }

        if (empty($token)) {
            return [];
        }

        $jwt = new JWTHandler($jwtConfig);

        return $jwt->get($token);
    }

    public function set_jwt(Array $data = []) {
        $jwtConfig = $this->config->get('jwt');

        $jwt = new JWTHandler($jwtConfig);

        if (empty($this->tokenData)) {
            $this->reviewToken($data['token'] ?? '');
        }
        
        if (empty($this->tokenData)) {
            $uuid = (string) Uuid::uuid4();
        }

        $data['session'] = $this->tokenData['session'] ?? $uuid;

        $token = $jwt->set($data);

        return $token;
    }

    private function findTokenFromHeader() {
        $jwtToken = explode(' ', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $token = $jwtToken[1] ?? false;

        return $token;
    }

    private function findTokenFromCookie() {
        $cookieConfig = $this->config->get('cookie');
        return $_COOKIE[$cookieConfig['name']] ?? false;
    }

    public function get_token(String $token = '') {
        $token = $this->findTokenFromHeader();

        if (empty($token)) {
            $token = $this->findTokenFromCookie();
        }

        return $token ?? '';
    }

    public function get_from_db(Array $data = []) {
        $mysqlConfig = $this->config->get('mysql');
        $mysql = new Mysql($mysqlConfig);

        $res = $mysql->get(['session_id' => $this->tokenData['session']]);

        if (!empty($res['data'])) {
            $resData = base64_decode($res['data']);
        } else {
            $resData = "";
        }

        return $resData;
    }

    public function save_to_db(Array $data = []) {
        $mysqlConfig = $this->config->get('mysql');
        $mysql = new Mysql($mysqlConfig);
        
        $data = base64_encode($data['data']);
        $res = $mysql->update(['session_id' => $this->tokenData['session'], 'data' => $data]);

        return true;
    }

    public function delete_from_db(Array $data = []) {
        $mysqlConfig = $this->config->get('mysql');
        $mysql = new Mysql($mysqlConfig);

        $res = $mysql->delete(['session_id' => $this->tokenData['session']]);

        return true;
    }

    public function delete_olds_from_db(Array $data = []) {
        $mysqlConfig = $this->config->get('mysql');
        $mysql = new Mysql($mysqlConfig);
        
        $maxLifeTime = $data['maxLifeTime'];

        $res = $mysql->delete_olds(['maxLifeTime' => $maxLifeTime]);

        return true;
    }

    private function get_from_cache(Array $data = []) {
        $redisConfig = $this->config->get('redis');
        $redis = new Redis($redisConfig);

        $res = $redis->get($this->tokenData['session']);

        $res = base64_decode($res);

        return $res;
    }

    public function save_to_cache(Array $data = []) {
        $redisConfig = $this->config->get('redis');
        $redis = new Redis($redisConfig);
        
        $data = $data['data'];
        
        $data = base64_encode($data);

        $redis->setex($this->tokenData['session'], $data, $redisConfig['cacheTime']);

        return true;
    }

    public function delete_from_cache() {
        $redisConfig = $this->config->get('redis');
        $redis = new Redis($redisConfig);

        $result = $redis->delete($this->tokenData['session']);
        
        return $result;
    }
}
