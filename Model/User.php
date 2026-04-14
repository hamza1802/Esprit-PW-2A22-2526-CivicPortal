<?php
/**
 * User.php — Model/User.php
 * Entité et accès aux données utilisateur via PDO.
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private int $id;
    private string $name;
    private string $email;
    private string $role;
    private ?string $passwordHash;
    private ?string $createdAt;

    public function __construct(int $id, string $name, string $email, string $role = 'citizen', ?string $passwordHash = null, ?string $createdAt = null) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setRole(string $role): void { $this->role = $role; }

    // Get display name without admin- prefix
    public function getDisplayName(): string {
        $name = $this->name;
        if (strpos($name, 'admin-') === 0) {
            return substr($name, 6);
        }
        return $name;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromRow(array $row): User {
        return new User(
            (int)$row['id'], 
            $row['username'] ?? $row['name'] ?? '',
            $row['email'] ?? '',
            $row['role'] ?? 'citizen',
            $row['password_hash'] ?? null,
            $row['created_at'] ?? null
        );
    }

    public static function fetchAll(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY id');
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => self::fromRow($row), $rows);
    }

    public static function findById(int $id): ?User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByEmail(string $email): ?User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
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

    public static function create(array $input): User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Handle name and role
        $name = trim($input['name']);
        $role = trim($input['role']) ?: 'citizen';
        
        // If name has admin- prefix, remove it and force role to admin
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

        return self::findById((int)$pdo->lastInsertId());
    }

    public static function update(int $id, array $input): bool {
        $pdo = Database::getInstance();

        // Handle admin- prefix
        $name = trim($input['name']);
        $role = trim($input['role']) ?: 'citizen';
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6); // Remove 'admin-' prefix
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

    public static function delete(int $id): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function authenticate(string $email, string $password): ?User {
        $user = self::findByEmail(trim($email));
        if ($user === null || $user->getPasswordHash() === null) {
            return null;
        }

        return password_verify($password, $user->getPasswordHash()) ? $user : null;
    }

    public static function validate(array $input, bool $isNew = true, ?int $userId = null): array {
        $errors = [];
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $role = trim($input['role'] ?? 'citizen');
        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        // Extract actual name from admin- prefix if present
        $actualName = $name;
        if (strpos($actualName, 'admin-') === 0) {
            $actualName = substr($actualName, 6);
        }

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif ($isNew && $role === 'admin' && strpos($name, 'admin-') !== 0) {
            $errors['name'] = 'cant register';
        } elseif (mb_strlen($actualName) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        } elseif (preg_match('/\d/', $actualName)) {
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

    public static function validateLogin(array $input): array {
        $errors = [];
        if (trim($input['email'] ?? '') === '') {
            $errors['email'] = 'Email is required.';
        }
        if (trim($input['password'] ?? '') === '') {
            $errors['password'] = 'Password is required.';
        }
        return $errors;
    }
}
?>
