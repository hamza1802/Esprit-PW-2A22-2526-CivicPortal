<?php
/**
 * UserController.php
 * Handles user authentication, registration, profile management,
 * profile picture BLOB upload, and admin user CRUD.
 *
 * SECURITY:
 *   - All queries use PDO prepared statements
 *   - Profile pictures validated for type/size before BLOB storage
 *   - is_active check prevents deactivated account login
 *   - Role escalation prevented via $allowAdmin flag
 */

require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Model/Profile.php';
require_once __DIR__ . '/../Model/Database.php';

class UserController {

    // =========================================================================
    // IMAGE UPLOAD CONSTANTS
    // =========================================================================
    private const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    // =========================================================================
    // USER DATA ACCESS
    // =========================================================================

    /**
     * Get all users with profile data for admin management.
     */
    public static function getAllUsers(): array {
        $pdo = Database::getInstance()->getConnection();
        $query = '
            SELECT u.id, u.username, u.email, u.role, u.created_at, u.is_active,
                   u.profile_pic IS NOT NULL AS has_pic,
                   p.bio, p.phone_number, p.date_of_birth
            FROM users u
            LEFT JOIN profile p ON u.id = p.user_id
            ORDER BY u.id
        ';
        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => [
            'user' => [
                'id' => (int)$row['id'],
                'name' => $row['username'],
                'email' => $row['email'],
                'role' => $row['role'],
                'created_at' => $row['created_at'],
                'is_active' => (bool)$row['is_active'],
                'has_profile_pic' => (bool)$row['has_pic'],
            ],
            'profile' => [
                'bio' => $row['bio'],
                'phone' => $row['phone_number'],
                'dob' => $row['date_of_birth']
            ]
        ], $rows);
    }

    public static function getUserById(int $id): ?User {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, is_active, profile_pic FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function getUserByEmail(string $email): ?User {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, is_active, profile_pic FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $pdo = Database::getInstance()->getConnection();
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
        $pdo = Database::getInstance()->getConnection();
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND id != :id');
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    // =========================================================================
    // USER RECORD CRUD
    // =========================================================================

    public static function createUserRecord(array $input): User {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);

        $name = trim($input['name']);
        $role = trim($input['role'] ?? '');
        if (empty($role)) {
            $role = 'citizen';
        }

        $stmt->execute([
            'username'      => $name,
            'email'         => trim($input['email']),
            'password_hash' => $passwordHash,
            'role'          => $role,
        ]);

        return self::getUserById((int)$pdo->lastInsertId());
    }

    public static function updateUserRecord(int $id, array $input): bool {
        $pdo  = Database::getInstance()->getConnection();
        $name = trim($input['name']);
        $role = trim($input['role'] ?? '');
        if (empty($role)) {
            $role = 'citizen';
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
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // =========================================================================
    // PROFILE PIC — BLOB UPLOAD
    // =========================================================================

    /**
     * Upload profile picture as BLOB to users table.
     * Validates file type (jpeg/png/webp) and size (≤ 2MB).
     */
    public static function uploadProfilePic(int $userId, array $file): array {
        // Validate file upload
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['errors' => ['profile_pic' => 'No file uploaded or upload error.']];
        }

        // Validate size
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            return ['errors' => ['profile_pic' => 'Image must be under 2MB.']];
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            return ['errors' => ['profile_pic' => 'Only JPEG, PNG, and WebP images are accepted.']];
        }

        // Read file and store as BLOB
        $blobData = file_get_contents($file['tmp_name']);
        if ($blobData === false) {
            return ['errors' => ['profile_pic' => 'Failed to read uploaded file.']];
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('UPDATE users SET profile_pic = :pic, profile_pic_mime = :mime WHERE id = :id');
        $stmt->bindParam(':pic', $blobData, PDO::PARAM_LOB);
        $stmt->bindParam(':mime', $mime, PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return ['success' => 'Profile picture updated.'];
    }

    // =========================================================================
    // ACCOUNT ACTIVATION / DEACTIVATION (Admin)
    // =========================================================================

    /**
     * Toggle user active status. Admin only.
     */
    public static function toggleUserActive(int $userId, bool $active): array {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('UPDATE users SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $active ? 1 : 0, 'id' => $userId]);
        $status = $active ? 'activated' : 'deactivated';
        return ['success' => "User {$status} successfully."];
    }

    /**
     * Get all active agents (for appointment slot management and assignment).
     */
    public static function getAgents(): array {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query(
            "SELECT id, username, email FROM users
             WHERE role = 'agent' AND is_active = 1
             ORDER BY username ASC"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // PROFILE DATA ACCESS
    // =========================================================================

    public static function getProfileByUserId(int $userId): ?Profile {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? Profile::fromRow($row) : null;
    }

    public static function createProfileRecord(int $userId): Profile {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('INSERT INTO profile (user_id) VALUES (:user_id)');
        $stmt->execute(['user_id' => $userId]);
        return self::getProfileByUserId($userId);
    }

    public static function ensureProfileExists(int $userId): ?Profile {
        $profile = self::getProfileByUserId($userId);
        if ($profile) {
            return $profile;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        if (!$stmt->fetch()) {
            return null;
        }
        
        return self::createProfileRecord($userId);
    }

    public static function updateProfileRecord(int $userId, array $data): bool {
        $pdo = Database::getInstance()->getConnection();
        $query = 'UPDATE profile SET first_name = :first_name, bio = :bio, phone_number = :phone_number, date_of_birth = :date_of_birth WHERE user_id = :user_id';
        $stmt = $pdo->prepare($query);
        $dateOfBirth = trim($data['date_of_birth'] ?? '');
        if (empty($dateOfBirth)) {
            $dateOfBirth = null;
        }

        return $stmt->execute([
            'first_name' => trim($data['first_name'] ?? ''),
            'bio' => trim($data['bio'] ?? ''),
            'phone_number' => trim($data['phone_number'] ?? ''),
            'date_of_birth' => $dateOfBirth,
            'user_id' => $userId,
        ]);
    }

    // =========================================================================
    // VALIDATION LOGIC
    // =========================================================================

    /**
     * Validate user input for create / update operations.
     *
     * @param bool $allowAdmin  Pass true only for admin-controlled actions (create_user /
     *                          update_user). Must NEVER be true for the public register action.
     *                          Prevents role escalation: citizens cannot self-assign admin role.
     */
    public static function validateUser(
        array  $input,
        bool   $isNew      = true,
        ?int   $userId     = null,
        bool   $allowAdmin = false
    ): array {
        $errors   = [];
        $name     = trim($input['name']     ?? '');
        $email    = trim($input['email']    ?? '');
        $role     = trim($input['role']     ?? '');
        if (empty($role)) {
            $role = 'citizen';
        }
        $password = $input['password']         ?? '';
        $confirm  = $input['confirm_password'] ?? '';

        // --- Name ---
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        } elseif (preg_match('/\d/', $name)) {
            $errors['name'] = 'Name must not contain numbers.';
        }

        // --- Email ---
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is not valid.';
        } elseif (self::emailExists($email, $userId)) {
            $errors['email'] = 'This email is already in use.';
        }

        // --- Role ---
        // SECURITY: 'admin' is only in the allowed set when the caller explicitly permits it
        $allowedRoles = ['citizen', 'agent'];
        if ($allowAdmin) {
            $allowedRoles[] = 'admin';
        }
        if (!in_array($role, $allowedRoles, true)) {
            $errors['role'] = 'The selected role is invalid.';
        }

        // --- Password ---
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

    // =========================================================================
    // CORE CONTROLLER ACTIONS
    // =========================================================================

    public static function login(array $input): array {
        $errors = self::validateUserLogin($input);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::getUserByEmail(trim($input['email']));
        if ($user === null || $user->getPasswordHash() === null || !password_verify($input['password'], $user->getPasswordHash())) {
            return ['errors' => ['email' => 'Invalid email or password.']];
        }

        // Check if account is deactivated
        if (!$user->isActive()) {
            return ['errors' => ['email' => 'This account has been deactivated. Contact an administrator.']];
        }

        // SECURITY: regenerate the session ID on privilege change (login).
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user->getId();
        $_SESSION['user_name']  = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role']  = $user->getRole();
        self::ensureProfileExists($user->getId());

        return ['success' => 'Login successful.', 'user' => $user];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function register(array $input): array {
        // SECURITY: $allowAdmin = false — citizens cannot self-assign admin role at registration.
        $errors = self::validateUser($input, true, null, false);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        self::updateProfileRecord($user->getId(), ['first_name' => trim($input['name'])]);

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

        $profileData = [
            'first_name' => $cleanName ?? $input['first_name'] ?? $profile->getFirstName(),
            'bio' => $input['bio'] ?? $profile->getBio(),
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
        // SECURITY: $allowAdmin = true — this action is only reachable by admins
        $errors = self::validateUser($input, true, null, true);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $user = self::createUserRecord($input);
        self::ensureProfileExists($user->getId());
        return ['success' => 'User created successfully.', 'user' => $user];
    }

    public static function deleteUser(int $id): array {
        if (!self::deleteUserRecord($id)) {
            return ['errors' => ['general' => 'Deletion failed.']];
        }

        return ['success' => 'User deleted successfully.'];
    }
}
