<?php
/**
 * Database.php — Model/Database.php
 * Singleton PDO database connection wrapper.
 */

class Database {
    private static $instance = null;
    private $conn;

    private $host = 'localhost';
    private $port = '3306';
    private $db   = 'civicportal';
    private $user = 'root';
    private $pass = '';

    private function __construct() {
        try {
            // First connect without DB to ensure it exists
            $pdo = new PDO("mysql:host={$this->host};port={$this->port}", $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            
            // Now connect to the specific DB
            $this->conn = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->db}", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
