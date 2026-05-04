<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    
    echo "Updating database schema...\n";
    
    // Add reset_token
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL");
    echo "reset_token column added or already exists.\n";
    
    // Add reset_expires
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expires DATETIME DEFAULT NULL");
    echo "reset_expires column added or already exists.\n";
    
    echo "Database schema updated successfully!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
