<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->query('SELECT email, otp_code, otp_expiry FROM users WHERE otp_code IS NOT NULL');
    $results = $stmt->fetchAll();

    if (empty($results)) {
        echo "No active OTP codes found in the database.\n";
    } else {
        echo "Active OTP Codes:\n";
        foreach ($results as $row) {
            echo "Email: {$row['email']} | OTP: {$row['otp_code']} | Expiry: {$row['otp_expiry']}\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
