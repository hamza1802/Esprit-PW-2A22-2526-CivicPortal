<?php
/**
 * import_schema.php
 * Imports the full parks&recreation schema from civicportal.sql
 * Uses CREATE TABLE IF NOT EXISTS to avoid overwriting existing transport data.
 */
require_once __DIR__ . '/Model/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = file_get_contents(__DIR__ . '/Model/civicportal.sql');
    
    // Replace CREATE TABLE with CREATE TABLE IF NOT EXISTS
    $sql = str_replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $sql);
    
    // Split by statement separator
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !str_starts_with(trim($s), '--') && !str_starts_with(trim($s), '/*')
    );
    
    $ok = 0;
    $errors = [];
    foreach ($statements as $stmt) {
        if (empty(trim($stmt))) continue;
        try {
            $db->exec($stmt);
            $ok++;
        } catch (PDOException $e) {
            // Skip harmless duplicate key errors
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                $errors[] = $e->getMessage() . " (SQL: " . substr($stmt, 0, 60) . "...)";
            }
        }
    }
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Imported $ok statements successfully.\n";
    if ($errors) {
        echo "Non-fatal errors:\n";
        foreach ($errors as $e) echo "  - $e\n";
    }
    
} catch (PDOException $e) {
    die("Fatal Error: " . $e->getMessage());
}
