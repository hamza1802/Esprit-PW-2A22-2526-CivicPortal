<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profile.php';
require_once __DIR__ . '/../config/database.php';

class UserController {
    // --- User Data Access ---

    public static function getAllUsers(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY id');
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => User::fromRow($row), $rows);
    }

    public static function getUserById(int $id): ?User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function getUserByEmail(string $email): ?User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $pdo = Database::getInstance();
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id != :id');
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function usernameExists(string $username, ?int $excludeId = null): bool {
        $pdo = Database::getInstance();
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND id != :id');
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function createUserRecord(array $input): User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        
        $name = trim($input['name']);
        $role = trim($input['role']) ?: 'citizen';
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6);
            $role = 'admin';
        }
        
        $stmt->execute([
            'username' => $name,
            'email' => trim($input['email']),
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        return self::getUserById((int)$pdo->lastInsertId());
    }

    public static function updateUserRecord(int $id, array $input): bool {
        $pdo = Database::getInstance();
        $name = trim($input['name']);
        $role = trim($input['role']) ?: 'citizen';
        
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6);
            $role = 'admin';
        }

        if (!empty($input['password'])) {
            $stmt = $pdo->prepare('UPDATE users SET username = :username, email = :email, role = :role, password_hash = :password_hash WHERE id = :id');
            return $stmt->execute([
                'username' => $name,
                'email' => trim($input['email']),
                'role' => $role,
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'id' => $id,
            ]);
        }

        $stmt = $pdo->prepare('UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id');
        return $stmt->execute([
            'username' => $name,
            'email' => trim($input['email']),
            'role' => $role,
            'id' => $id,
        ]);
    }

    public static function deleteUserRecord(int $id): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // --- Profile Data Access ---

    public static function getProfileByUserId(int $userId): ?Profile {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? Profile::fromRow($row) : null;
    }

    public static function createProfileRecord(int $userId): Profile {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO profile (user_id) VALUES (:user_id)');
        $stmt->execute(['user_id' => $userId]);
        return self::getProfileByUserId($userId);
    }

    public static function ensureProfileExists(int $userId): ?Profile {
        $profile = self::getProfileByUserId($userId);
        if ($profile) {
            return $profile;
        }
        
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        if (!$stmt->fetch()) {
            return null;
        }
        
        return self::createProfileRecord($userId);
    }

    public static function updateProfileRecord(int $userId, array $data): bool {
        $pdo = Database::getInstance();
        $query = 'UPDATE profile SET first_name = :first_name, bio = :bio, avatar_url = :avatar_url, phone_number = :phone_number, date_of_birth = :date_of_birth WHERE user_id = :user_id';
        $stmt = $pdo->prepare($query);
        $dateOfBirth = trim($data['date_of_birth'] ?? '');
        if (empty($dateOfBirth)) {
            $dateOfBirth = null;
        }

        return $stmt->execute([
            'first_name' => trim($data['first_name'] ?? ''),
            'bio' => trim($data['bio'] ?? ''),
            'avatar_url' => trim($data['avatar_url'] ?? ''),
            'phone_number' => trim($data['phone_number'] ?? ''),
            'date_of_birth' => $dateOfBirth,
            'user_id' => $userId,
        ]);
    }

    // --- Validation Logic ---

    public static function validateUser(array $input, bool $isNew = true, ?int $userId = null): array {
        $errors = [];
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $role = trim($input['role'] ?? 'citizen');
        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        } elseif (preg_match('/\d/', $name)) {
            $errors['name'] = 'Name must not contain numbers.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is not valid.';
        } elseif (self::emailExists($email, $userId)) {
            $errors['email'] = 'This email is already in use.';
        }

        $allowedRoles = ['citizen', 'agent', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            $errors['role'] = 'The selected role is invalid.';
        }

        if ($isNew || $password !== '') {
            if (mb_strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long.';
            }
            if ($password !== $confirm) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
        }

        return $errors;
    }

    public static function validateUserLogin(array $input): array {
        $errors = [];
        if (trim($input['email'] ?? '') === '') {
            $errors['email'] = 'Email is required.';
        }
        if (trim($input['password'] ?? '') === '') {
            $errors['password'] = 'Password is required.';
        }
        return $errors;
    }

    // --- Core Controller Actions ---

    public static function login(array $input): array {
        $errors = self::validateUserLogin($input);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::getUserByEmail(trim($input['email']));
        if ($user === null || $user->getPasswordHash() === null || !password_verify($input['password'], $user->getPasswordHash())) {
            return ['errors' => ['email' => 'Invalid email or password.']];
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        self::ensureProfileExists($user->getId());

        return ['success' => 'Login successful.', 'user' => $user];
    }

    public static function logout(): void {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_email']);
    }

    public static function register(array $input): array {
        $errors = self::validateUser($input, true);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        
        $cleanName = trim($input['name']);
        if (strpos($cleanName, 'admin-') === 0) {
            $cleanName = substr($cleanName, 6);
        }
        self::updateProfileRecord($user->getId(), ['first_name' => $cleanName]);
        
        return ['success' => 'Registration successful.', 'user' => $user];
    }

    public static function updateProfile(int $id, array $input): array {
        $user = self::getUserById($id);
        if ($user === null) {
            return ['errors' => ['general' => 'User not found.']];
        }

        if (!isset($input['role']) || trim($input['role']) === '') {
            $input['role'] = $user->getRole();
        }

        $errors = self::validateUser($input, false, $id);
        if (!empty($input['first_name']) && preg_match('/\d/', $input['first_name'])) {
            $errors['first_name'] = 'First name must not contain numbers.';
        }
        if (!empty($input['last_name']) && preg_match('/\d/', $input['last_name'])) {
            $errors['last_name'] = 'Last name must not contain numbers.';
        }
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $updated = self::updateUserRecord($id, $input);
        if (!$updated) {
            return ['errors' => ['general' => 'Unable to update profile.']];
        }

        $updatedUser = self::getUserById($id);
        $profile = self::ensureProfileExists($id);
        
        $cleanName = trim($input['name']);
        if (strpos($cleanName, 'admin-') === 0) {
            $cleanName = substr($cleanName, 6);
        }
        
        $profileData = [
            'first_name' => $cleanName ?? $input['first_name'] ?? $profile->getFirstName(),
            'last_name' => $input['last_name'] ?? $profile->getLastName(),
            'bio' => $input['bio'] ?? $profile->getBio(),
            'avatar_url' => $input['avatar_url'] ?? $profile->getAvatarUrl(),
            'phone_number' => $input['phone_number'] ?? $profile->getPhoneNumber(),
            'date_of_birth' => $input['date_of_birth'] ?? $profile->getDateOfBirth(),
        ];
        self::updateProfileRecord($id, $profileData);

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $id) {
            $_SESSION['user_name'] = $updatedUser ? $updatedUser->getDisplayName() : $cleanName;
            if (!empty($input['email'])) {
                $_SESSION['user_email'] = $input['email'];
            }
        }

        return ['success' => 'Profile updated successfully.'];
    }

    public static function createUser(array $input): array {
        $errors = self::validateUser($input, true);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        return ['success' => 'User added successfully.', 'user' => $user];
    }

    public static function deleteUser(int $id): array {
        if (!self::deleteUserRecord($id)) {
            return ['errors' => ['general' => 'Deletion failed.']];
        }

        return ['success' => 'User deleted successfully.'];
    }
}

