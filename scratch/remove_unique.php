<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $pdo = Database::getInstance();
    // Check if the unique index exists and drop it
    $pdo->exec("ALTER TABLE users DROP INDEX username");
    echo "Successfully removed unique constraint from 'username'. You can now have duplicate names.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
