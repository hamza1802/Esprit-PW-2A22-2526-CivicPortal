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
            // Connect directly to the database with improved settings
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5, // Connection timeout
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log the error and provide a user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database Connection Error: Please ensure MySQL is running and configured correctly.");
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
