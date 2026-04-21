<?php
/**
 * config/seed.php
 * Database seeder to ensure essential users exist.
 */

require_once __DIR__ . '/database.php';

function seedAdmin() {
    $pdo = Database::getInstance();
    $email = 'admin@gmail.com';
    $password = '01472583';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $username = 'Administrator';
    $role = 'admin';

    // Check if user already exists
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Admin user $email already exists.\n";
        // Verify password
        if (password_verify($password, $existing['password_hash'])) {
            echo "Password for $email is ALREADY CORRECT.\n";
        } else {
            echo "Password for $email is INCORRECT. Updating it now...\n";
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE email = :email');
            $stmt->execute(['hash' => $passwordHash, 'email' => $email]);
            echo "Password updated.\n";
        }
        return;
    }

    try {
        $pdo->beginTransaction();

        // Create User
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create Profile
        $stmt = $pdo->prepare('INSERT INTO profile (user_id, first_name) VALUES (:user_id, :first_name)');
        $stmt->execute([
            'user_id' => $userId,
            'first_name' => 'Admin'
        ]);
        
        $pdo->commit();
        echo "Admin user $email created successfully with password $password.\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error seeding database: " . $e->getMessage() . "\n";
    }
}

// Only run if executing via CLI or explicitly called
if (php_sapi_name() === 'cli') {
    seedAdmin();
}
