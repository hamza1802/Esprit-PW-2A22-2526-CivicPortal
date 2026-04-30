<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profile.php';
require_once __DIR__ . '/../config/database.php';

class UserController {
    // --- User Data Access ---

    public static function getAllUsers(string $search = '', string $orderBy = 'id DESC'): array {
        $pdo = Database::getInstance();
        $params = [];
        $where = '';

        if (!empty($search)) {
            $where = " WHERE u.username LIKE :s1 OR u.email LIKE :s2 OR p.first_name LIKE :s3 ";
            $searchTerm = '%' . $search . '%';
            $params['s1'] = $searchTerm;
            $params['s2'] = $searchTerm;
            $params['s3'] = $searchTerm;
        }

        // Validate orderBy to prevent SQL injection
        $allowedOrders = [
            'id DESC', 'id ASC', 
            'username ASC', 'username DESC', 
            'email ASC', 'role ASC', 
            'created_at DESC',
            'p.first_name ASC', 'p.first_name DESC'
        ];
        if (!in_array($orderBy, $allowedOrders)) {
            $orderBy = 'id DESC';
        }

        $query = "
            SELECT u.id, u.username, u.email, u.role, u.created_at,
                   p.first_name, p.bio, p.phone_number, p.date_of_birth, p.avatar_url
            FROM users u
            LEFT JOIN profile p ON u.id = p.user_id
            $where
            ORDER BY $orderBy
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => [
            'user' => User::fromRow($row),
            'profile' => [
                'first_name' => $row['first_name'],
                'bio' => $row['bio'],
                'phone' => $row['phone_number'],
                'dob' => $row['date_of_birth'],
                'avatar' => $row['avatar_url']
            ]
        ], $rows);
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
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function usernameExists(string $username, ?int $excludeId = null): bool {
        $pdo = Database::getInstance();
        $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
        $params = ['username' => $username];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function createUserRecord(array $input): User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $passwordHash = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);
        
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
        $name = trim($input['name'] ?? '');
        
        // Fetch current user to preserve values if missing in input
        $currentUser = self::getUserById($id);
        if (!$currentUser) return false;

        $email = trim($input['email'] ?? $currentUser->getEmail());
        $role = $input['role'] ?? $currentUser->getRole();
        
        // Admin override prefix logic
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6);
            $role = 'admin';
        }

        $params = [
            'username' => empty($name) ? $currentUser->getDisplayName() : $name,
            'email' => $email,
            'role' => $role,
            'id' => $id
        ];

        $sql = 'UPDATE users SET username = :username, email = :email, role = :role';
        
        if (!empty($input['password'])) {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function deleteUserRecord(int $id): bool {
        $pdo = Database::getInstance();
        // Mandatory profile cleanup for foreign keys
        $pdo->prepare('DELETE FROM profile WHERE user_id = :id')->execute(['id' => $id]);
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function getUserStats(): array {
        $pdo = Database::getInstance();
        $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $stmt = $pdo->query($query);
        $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return [
            'total' => array_sum($stats),
            'admin' => $stats['admin'] ?? 0,
            'agent' => $stats['agent'] ?? 0,
            'citizen' => $stats['citizen'] ?? 0
        ];
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
        $stmt = $pdo->prepare('INSERT INTO profile (user_id, first_name) SELECT id, username FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return self::getProfileByUserId($userId);
    }

    public static function ensureProfileExists(int $userId): ?Profile {
        $profile = self::getProfileByUserId($userId);
        if ($profile) return $profile;
        return self::createProfileRecord($userId);
    }

    public static function updateProfileRecord(int $userId, array $data): bool {
        $pdo = Database::getInstance();
        $fields = [];
        $params = ['user_id' => $userId];

        // Map potential input keys to database columns
        if (isset($data['first_name']) || isset($data['name'])) {
            $fields[] = 'first_name = :first_name';
            $params['first_name'] = trim($data['first_name'] ?? $data['name']);
        }
        if (isset($data['bio'])) {
            $fields[] = 'bio = :bio';
            $params['bio'] = trim($data['bio']);
        }
        if (isset($data['avatar_url'])) {
            $fields[] = 'avatar_url = :avatar_url';
            $params['avatar_url'] = trim($data['avatar_url']);
        }
        if (isset($data['phone_number']) || isset($data['phone'])) {
            $fields[] = 'phone_number = :phone_number';
            $params['phone_number'] = trim($data['phone_number'] ?? $data['phone']);
        }
        if (isset($data['date_of_birth']) || isset($data['dob'])) {
            $fields[] = 'date_of_birth = :date_of_birth';
            $val = $data['date_of_birth'] ?? $data['dob'];
            $params['date_of_birth'] = !empty($val) ? $val : null;
        }

        if (empty($fields)) {
            return true;
        }

        $query = 'UPDATE profile SET ' . implode(', ', $fields) . ' WHERE user_id = :user_id';
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    }

    // --- Validation Logic ---

    public static function validateUser(array $input, bool $isNew = true, ?int $userId = null): array {
        $errors = [];
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is not valid.';
        } elseif (self::emailExists($email, $userId)) {
            $errors['email'] = 'This email is already in use.';
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
        if (trim($input['email'] ?? '') === '') $errors['email'] = 'Email is required.';
        if (trim($input['password'] ?? '') === '') $errors['password'] = 'Password is required.';
        return $errors;
    }

    // --- Actions ---

    public static function login(array $input): array {
        $errors = self::validateUserLogin($input);
        if (!empty($errors)) return ['errors' => $errors];

        $user = self::getUserByEmail(trim($input['email']));
        if (!$user || !password_verify($input['password'], $user->getPasswordHash())) {
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
        session_destroy();
    }

    public static function register(array $input): array {
        $errors = self::validateUser($input, true);
        if (!empty($errors)) return ['errors' => $errors];

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        self::updateProfileRecord($user->getId(), $input);
        
        return ['success' => 'Registration successful.', 'user' => $user];
    }

    public static function updateProfile(int $id, array $input): array {
        $errors = self::validateUser($input, false, $id);
        if (!empty($errors)) return ['errors' => $errors];

        if (!self::updateUserRecord($id, $input)) {
            return ['errors' => ['general' => 'Update failed.']];
        }

        self::ensureProfileExists($id);
        self::updateProfileRecord($id, $input);

        $updatedUser = self::getUserById($id);
        if ($updatedUser && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
            $_SESSION['user_name'] = $updatedUser->getDisplayName();
            $_SESSION['user_email'] = $updatedUser->getEmail();
        }

        return ['success' => 'Profile updated successfully.', 'user' => $updatedUser];
    }

    public static function createUser(array $input): array {
        $errors = self::validateUser($input, true);
        if (!empty($errors)) return ['errors' => $errors];

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        self::updateProfileRecord($user->getId(), $input);
        
        return ['success' => 'User added successfully.', 'user' => $user, 'user_id' => $user->getId()];
    }

    public static function deleteUser(int $id): array {
        if (!self::deleteUserRecord($id)) return ['errors' => ['general' => 'Deletion failed.']];
        return ['success' => 'User deleted successfully.'];
    }
}
