<?php
/**
 * Database.php
 * MySQLi Database Connection Manager
 */

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            $host = '127.0.0.1';
            $username = 'root';
            // Default XAMPP password is empty
            $password = '';
            $database = 'civicportal';

            self::$connection = new mysqli($host, $username, $password, $database);

            if (self::$connection->connect_error) {
                die("Connection failed: " . self::$connection->connect_error);
            }
        }
        return self::$connection;
    }
}
?>
