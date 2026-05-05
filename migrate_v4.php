<?php
/**
 * migrate_v4.php
 * CivicPortal Database Migration v4
 *
 * Adds BLOB image columns to transport_type table.
 *
 * Run once via browser: http://localhost/CivicPortal/migrate_v4.php
 */

require_once __DIR__ . '/Model/Database.php';

$db = Database::getInstance()->getConnection();

echo "<pre>\n=== CivicPortal Migration v4 ===\n\n";

$migrations = [
    "ALTER TABLE `transport_type` ADD COLUMN `type_image`      MEDIUMBLOB"                          => 'transport_type.type_image',
    "ALTER TABLE `transport_type` ADD COLUMN `type_image_mime` VARCHAR(50) DEFAULT 'image/jpeg'"    => 'transport_type.type_image_mime',
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

echo "\n=== Migration v4 complete! ===\n</pre>";
?>
