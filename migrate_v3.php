<?php
/**
 * migrate_v3.php
 * CivicPortal Database Migration v3
 * 
 * Adds BLOB image columns, appointments system, notifications,
 * and drops the complaints table.
 *
 * Run once via browser: http://localhost/CivicPortal/migrate_v3.php
 */

require_once __DIR__ . '/Model/Database.php';

$db = Database::getInstance()->getConnection();

echo "<pre>\n";
echo "=== CivicPortal Migration v3 ===\n\n";

$migrations = [
    // ─────────────────────────────────────────────────────────────────────────
    // USERS TABLE — Profile pic BLOB + active status
    // ─────────────────────────────────────────────────────────────────────────
    "ALTER TABLE `users` ADD COLUMN `profile_pic` MEDIUMBLOB" => 'users.profile_pic',
    "ALTER TABLE `users` ADD COLUMN `profile_pic_mime` VARCHAR(50) DEFAULT 'image/jpeg'" => 'users.profile_pic_mime',
    "ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1" => 'users.is_active',

    // ─────────────────────────────────────────────────────────────────────────
    // REQUESTS TABLE — Enhanced service requests with BLOB attachment
    // ─────────────────────────────────────────────────────────────────────────
    "ALTER TABLE `requests` ADD COLUMN `description` TEXT AFTER `title`" => 'requests.description',
    "ALTER TABLE `requests` ADD COLUMN `category` VARCHAR(100) AFTER `description`" => 'requests.category',
    "ALTER TABLE `requests` ADD COLUMN `attachment` MEDIUMBLOB" => 'requests.attachment',
    "ALTER TABLE `requests` ADD COLUMN `attachment_mime` VARCHAR(50)" => 'requests.attachment_mime',
    "ALTER TABLE `requests` ADD COLUMN `assigned_to` INT(11) DEFAULT NULL" => 'requests.assigned_to',
    "ALTER TABLE `requests` ADD COLUMN `status_updated_at` DATETIME DEFAULT NULL" => 'requests.status_updated_at',

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSPORT TABLE — Vehicle image BLOB
    // ─────────────────────────────────────────────────────────────────────────
    "ALTER TABLE `transport` ADD COLUMN `vehicle_image` MEDIUMBLOB" => 'transport.vehicle_image',
    "ALTER TABLE `transport` ADD COLUMN `vehicle_image_mime` VARCHAR(50) DEFAULT 'image/jpeg'" => 'transport.vehicle_image_mime',

    // ─────────────────────────────────────────────────────────────────────────
    // PROGRAM TABLE — Program image BLOB
    // ─────────────────────────────────────────────────────────────────────────
    "ALTER TABLE `program` ADD COLUMN `program_image` MEDIUMBLOB" => 'program.program_image',
    "ALTER TABLE `program` ADD COLUMN `program_image_mime` VARCHAR(50) DEFAULT 'image/jpeg'" => 'program.program_image_mime',
];

foreach ($migrations as $sql => $label) {
    try {
        $db->exec($sql);
        echo "✅ Added: {$label}\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⏭️  Skipped (already exists): {$label}\n";
        } else {
            echo "❌ FAILED: {$label} — " . $e->getMessage() . "\n";
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// APPOINTMENTS TABLE (NEW)
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Creating appointments table ---\n";
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `appointments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `service_type` VARCHAR(100) NOT NULL,
            `preferred_date` DATE NOT NULL,
            `preferred_time` TIME NOT NULL,
            `notes` TEXT DEFAULT NULL,
            `status` ENUM('pending','confirmed','rescheduled','cancelled','completed') NOT NULL DEFAULT 'pending',
            `assigned_to` INT(11) DEFAULT NULL,
            `reschedule_reason` TEXT DEFAULT NULL,
            `new_date` DATE DEFAULT NULL,
            `new_time` TIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `assigned_to` (`assigned_to`),
            CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✅ Created: appointments\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Skipped (already exists): appointments\n";
    } else {
        echo "❌ FAILED: appointments — " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// APPOINTMENT SLOTS TABLE (NEW)
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Creating appointment_slots table ---\n";
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `appointment_slots` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `agent_id` INT(11) NOT NULL,
            `service_type` VARCHAR(100) NOT NULL,
            `day_of_week` TINYINT(1) NOT NULL COMMENT '0=Sun,1=Mon,...,6=Sat',
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `agent_id` (`agent_id`),
            CONSTRAINT `slots_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✅ Created: appointment_slots\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Skipped (already exists): appointment_slots\n";
    } else {
        echo "❌ FAILED: appointment_slots — " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// NOTIFICATIONS TABLE (NEW)
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Creating notifications table ---\n";
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `body` TEXT DEFAULT NULL,
            `type` ENUM('appointment','request','system','info') NOT NULL DEFAULT 'info',
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✅ Created: notifications\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Skipped (already exists): notifications\n";
    } else {
        echo "❌ FAILED: notifications — " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// PROGRAM_CATEGORY TABLE (NEW) + seed from existing program.category values
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Creating program_category table ---\n";
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `program_category` (
            `id`         INT(11)      NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(100) NOT NULL,
            `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_cat_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "✅ Created: program_category\n";
    // Seed from existing program.category values
    $db->exec("INSERT IGNORE INTO `program_category` (name)
               SELECT DISTINCT category FROM `program`
               WHERE category IS NOT NULL AND TRIM(category) != ''");
    echo "✅ Seeded: program_category from program.category\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Skipped (already exists): program_category\n";
    } else {
        echo "❌ FAILED: program_category — " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// ENROLLMENT — update status enum to include 'pending'
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Updating enrollment.status enum ---\n";
try {
    $db->exec("ALTER TABLE `enrollment`
               MODIFY COLUMN `status`
               ENUM('pending','confirmed','waitlisted','cancelled') NOT NULL DEFAULT 'pending'");
    echo "✅ Updated: enrollment.status enum (added 'pending')\n";
} catch (PDOException $e) {
    echo "⚠️  Enrollment status note: " . $e->getMessage() . "\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// DROP COMPLAINTS TABLE
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Dropping complaints table ---\n";
try {
    $db->exec("DROP TABLE IF EXISTS `complaints`");
    echo "✅ Dropped: complaints\n";
} catch (PDOException $e) {
    echo "❌ FAILED to drop complaints — " . $e->getMessage() . "\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// ADD FK for requests.assigned_to
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Adding FK for requests.assigned_to ---\n";
try {
    $db->exec("ALTER TABLE `requests` ADD CONSTRAINT `requests_assigned_fk` 
               FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL");
    echo "✅ Added FK: requests.assigned_to → users.id\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Skipped (already exists): requests.assigned_to FK\n";
    } else {
        echo "⚠️  FK note: " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// UPDATE requests status ENUM to include new statuses
// ─────────────────────────────────────────────────────────────────────────────
echo "\n--- Updating requests.status ENUM ---\n";
try {
    $db->exec("ALTER TABLE `requests` MODIFY COLUMN `status` 
               ENUM('pending','in_progress','approved','rejected','validated','resolved') 
               NOT NULL DEFAULT 'pending'");
    echo "✅ Updated: requests.status ENUM\n";
} catch (PDOException $e) {
    echo "⚠️  Status enum note: " . $e->getMessage() . "\n";
}

echo "\n=== Migration v3 complete! ===\n";
echo "</pre>";
?>
