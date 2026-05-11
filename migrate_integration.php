<?php
require_once __DIR__ . '/Model/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    $columns = [
        'two_fa_enabled' => "ALTER TABLE users ADD COLUMN two_fa_enabled TINYINT(1) NOT NULL DEFAULT 0",
        'otp_code'       => "ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL",
        'otp_expiry'     => "ALTER TABLE users ADD COLUMN otp_expiry DATETIME DEFAULT NULL",
        'reset_token'    => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL",
        'reset_expires'  => "ALTER TABLE users ADD COLUMN reset_expires DATETIME DEFAULT NULL"
    ];

    foreach ($columns as $col => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Added column: $col\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⏭️  Column $col already exists.\n";
            } else {
                echo "❌ Error adding $col: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Migration complete.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
