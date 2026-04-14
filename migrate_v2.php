<?php
/**
 * migrate_v2.php
 * Updates the database schema for the refined Parks & Rec specifications.
 */

require_once __DIR__ . '/Model/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Add 'image' column to program table if it doesn't exist
    $db->exec("ALTER TABLE program ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'default.jpg'");
    echo "Program table updated (image column).<br>";

    // 2. Add 'pending' to enrollment status enum
    // Note: ALTER TABLE on ENUM requires re-specifying the whole list in many versions of MariaDB/MySQL
    // We will ensure 'pending' is first to be the default if needed, or just add it to the list.
    $db->exec("ALTER TABLE enrollment MODIFY COLUMN status ENUM('pending', 'confirmed', 'waitlisted', 'cancelled') NOT NULL DEFAULT 'pending'");
    echo "Enrollment table updated (added 'pending' status).<br>";

    echo "Migration complete.";

} catch (PDOException $e) {
    die("Migration Error: " . $e->getMessage());
}
?>
