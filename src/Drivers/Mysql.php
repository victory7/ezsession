<?php
namespace EzSession\Drivers;

use DateTime;
use PDO;
use PDOException;
use function getUserIp;

class Mysql {
    private $host = '127.0.0.1';
    private $port = '3306';
    private $user = '';
    private $pass = '';
    private $db   = '';
    private $tbl  = '';
    private $dsn  = '';
    private $charset = 'utf8mb4';
    private $conn = '';

    public function __construct(array $config = []) {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->user = $config['user'];
        $this->pass = $config['password'];
        $this->db   = $config['database'];
        $this->tbl  = $config['table'];

        $this->dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Error mode to throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Set default fetch mode to associative array
            PDO::ATTR_EMULATE_PREPARES   => false,                 // Disable emulation of prepared statements
        ];
        
        try {
            $this->conn = new PDO($this->dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    function __destruct() {
        $this->conn = null;
    }

    public function get(array $data = []) {
        $sessionID = $data['session_id'];
        

        $sql = "SELECT * FROM $this->tbl WHERE session_id = :session_id";
        try {
            // Select
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['session_id' => $sessionID]);
            $user = $stmt->fetch();

            return $user;

        } catch (PDOException $e) {
            // Table not exist
            if ($e->getCode() === '42S02') {
                // Create Table
                $this->initTable();

                // Run again
                return $this->get($data);
            } else {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

    }

    public function add(array $data = []) {

        $sessionID   = $data['session_id'];
        $ip          = getUserIp();
        $sessionData = $data['data'] ?? null;

        try {
            // Insert
            $sql = "INSERT INTO $this->tbl (session_id, ip, data) VALUES (:session_id, :ip, :data)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['session_id' => $sessionID, 'ip' => $ip, 'data' => $sessionData]);

            return $sessionID;

        } catch (PDOException $e) {
            $errCode = $e->getCode();
            // Table not exist
            if ($errCode === '42S02') {
                // Create Table
                $this->initTable();

                // Run again
                return $this->add($data);

            // Update
            } elseif ($errCode === '23000') {
                // return $this->update($data);
            } else {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }

    public function update(array $data = []) {
        $sessionID = $data['session_id'];

        try {
            // Update
            $sql = "UPDATE $this->tbl SET data = :data WHERE session_id = :session_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['session_id' => $sessionID, 'data' => $data['data']]);

            // Get the number of affected rows
            $affectedRows = $stmt->rowCount();
            if ($affectedRows === 0) {
                return $this->add($data);
            }

            return true;

        } catch (PDOException $e) {
            $errCode = $e->getCode();
            // Table not exist
            if ($errCode === '42S02') {
                // Create Table
                $this->initTable();

                // Run again
                return $this->add($data);
            } else {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }

    public function delete(array $data = []) {
        $sessionID = $data['session_id'];

        try {
            $sql = "DELETE FROM $this->tbl WHERE session_id = :session_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['session_id' => $sessionID]);

            // Get the number of affected rows
            $affectedRows = $stmt->rowCount();
            return $affectedRows > 0;

        } catch (PDOException $e) {
            $errCode = $e->getCode();
            
            throw new PDOException($e->getMessage(), (int)$e->getCode());
            
        }
    }

    public function delete_olds(array $data = []) {
        $maxLifeTime = $data['maxLifeTime'];

        try {
            $sql = "DELETE FROM $this->tbl WHERE created_at < (now() - interval :maxLifeTime second)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['maxLifeTime' => $maxLifeTime]);

            // Get the number of affected rows
            $affectedRows = $stmt->rowCount();
            return $affectedRows > 0;

        } catch (PDOException $e) {
            $errCode = $e->getCode();
            
            throw new PDOException($e->getMessage(), (int)$e->getCode());
            
        }
    }

    public function initTable() {

        // SQL query to create a table
        $sql = "CREATE TABLE IF NOT EXISTS $this->tbl (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL UNIQUE,
            ip VARCHAR(20) NOT NULL,
            data TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Execute the query using exec()
        $this->conn->exec($sql);
    }
}