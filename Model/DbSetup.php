<?php
/**
 * DbSetup.php — Model/DbSetup.php
 * Final robust seeding for CivicPortal.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/Database.php';

function setupDatabase() {
    $db = Database::getInstance()->getConnection();
    
    // 1. Clean existing tables in correct order
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("TRUNCATE TABLE enrollment");
    $db->exec("TRUNCATE TABLE requests");
    $db->exec("TRUNCATE TABLE users");
    $db->exec("TRUNCATE TABLE program");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Tables truncated.<br>";

    // 2. Seed Users
    $pass = password_hash('password123', PASSWORD_DEFAULT);
    $uStmt = $db->prepare("INSERT INTO users (id, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
    $uStmt->execute([1, 'john_citizen', 'john@example.com', $pass, 'citizen']);
    $uStmt->execute([2, 'alice_worker', 'alice@cityhall.gov', $pass, 'agent']);
    $uStmt->execute([3, 'admin_user', 'admin@cityhall.gov', $pass, 'admin']);
    echo "Users seeded (1, 2, 3).<br>";

    // 3. Seed Programs
    $pStmt = $db->prepare("INSERT INTO program (id, title, description, category, capacity, location, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $pStmt->execute([101, 'Summer Pottery Workshop', 'Hands-on ceramics for all skill levels.', 'Arts', 20, 'Cultural Center', 'active', 'default.jpg']);
    $pStmt->execute([102, 'Youth Swimming Program', 'Beginner to advanced swimming lessons.', 'Sports', 50, 'Municipal Pool', 'active', 'default.jpg']);
    $pStmt->execute([103, 'Community Gardening', 'Learn sustainable urban farming.', 'Environment', 2, 'North Park', 'active', 'default.jpg']);
    echo "Programs seeded (101, 102, 103).<br>";

    echo "Setup complete. CivicPortal is ready for verification.";
}

setupDatabase();
?>
