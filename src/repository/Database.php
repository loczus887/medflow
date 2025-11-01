<?php

class Database {
    
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'medflow_db';
        $user = getenv('DB_USER') ?: 'medflow_user';
        $password = getenv('DB_PASSWORD') ?: 'medflow_pass';
        
        try {
            $this->connection = new PDO(
                "pgsql:host=$host;port=$port;dbname=$dbname",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}