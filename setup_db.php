<?php
require_once __DIR__ . '/Model/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Add FKs that failed - ignore if they already exist
    $fks = [
        "ALTER TABLE `enrollment` ADD CONSTRAINT IF NOT EXISTS `enrollment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",
        "ALTER TABLE `enrollment` ADD CONSTRAINT IF NOT EXISTS `enrollment_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE",
        "ALTER TABLE `requests` ADD CONSTRAINT IF NOT EXISTS `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",
        "ALTER TABLE `ticket` ADD CONSTRAINT IF NOT EXISTS `ticket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",
    ];
    foreach ($fks as $fk) {
        try { $db->exec($fk); } catch (Exception $e) {}
    }

    // Also add pending status to enrollment if missing
    try {
        $db->exec("ALTER TABLE `enrollment` MODIFY COLUMN `status` ENUM('pending','confirmed','waitlisted','cancelled') NOT NULL DEFAULT 'pending'");
    } catch (Exception $e) {}

    // Add image column to program if missing
    try {
        $db->exec("ALTER TABLE `program` ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) DEFAULT 'default.jpg'");
    } catch (Exception $e) {}

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Seed a demo admin user (password: admin123)
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO users (id, username, email, password_hash, role) VALUES (3, 'Admin User', 'admin@cityhall.gov', ?, 'admin')");
    $stmt->execute([$hash]);

    // Seed a demo worker user (password: worker123)  
    $hash2 = password_hash('worker123', PASSWORD_DEFAULT);
    $stmt2 = $db->prepare("INSERT IGNORE INTO users (id, username, email, password_hash, role) VALUES (2, 'Alice Worker', 'alice@cityhall.gov', ?, 'worker')");
    $stmt2->execute([$hash2]);

    // Seed a demo citizen user
    $hash3 = password_hash('citizen123', PASSWORD_DEFAULT);
    $stmt3 = $db->prepare("INSERT IGNORE INTO users (id, username, email, password_hash, role) VALUES (1, 'John Citizen', 'john@example.com', ?, 'citizen')");
    $stmt3->execute([$hash3]);

    echo "Setup complete!\n";
    
    // List tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
    $users = $db->query("SELECT id, username, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "Users: ";
    foreach ($users as $u) echo "[{$u['id']}] {$u['username']} ({$u['role']}) ";
    echo "\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
