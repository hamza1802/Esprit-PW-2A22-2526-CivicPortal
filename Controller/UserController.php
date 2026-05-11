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
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    public static function getAllUsers(string $search = '', string $sort = 'u.id DESC'): array {
        $pdo = Database::getInstance()->getConnection();
        $params = [];
        $where = '';

        if (!empty($search)) {
            $where = " WHERE u.username LIKE :s1 OR u.email LIKE :s2 OR p.bio LIKE :s3 ";
            $searchTerm = '%' . $search . '%';
            $params['s1'] = $searchTerm;
            $params['s2'] = $searchTerm;
            $params['s3'] = $searchTerm;
        }

        // Validate sort to prevent SQL injection
        $allowedSorts = [
            'u.id DESC', 'u.id ASC', 
            'u.username ASC', 'u.username DESC', 
            'u.email ASC', 'u.email DESC',
            'u.role ASC', 'u.created_at DESC'
        ];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'u.id DESC';
        }

        $query = "
            SELECT u.id, u.username, u.email, u.role, u.created_at, u.is_active,
                   u.two_fa_enabled, u.otp_code, u.otp_expiry,
                   u.profile_pic IS NOT NULL AS has_pic,
                   p.bio, p.phone_number, p.date_of_birth, p.avatar_url
            FROM users u
            LEFT JOIN profile p ON u.id = p.user_id
            $where
            ORDER BY $sort
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => [
            'id' => (int)$row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'role' => $row['role'],
            'created_at' => $row['created_at'],
            'is_active' => (bool)$row['is_active'],
            'has_pic' => (bool)$row['has_pic'],
            'profile' => [
                'bio' => $row['bio'],
                'phone' => $row['phone_number'],
                'dob' => $row['date_of_birth'],
                'avatar' => $row['avatar_url']
            ]
        ], $rows);
    }

    public static function getUserById(int $id): ?User {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, is_active, profile_pic, two_fa_enabled, otp_code, otp_expiry FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function getUserByEmail(string $email): ?User {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, is_active, profile_pic, two_fa_enabled, otp_code, otp_expiry FROM users WHERE email = :email');
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
        $name = trim($input['name'] ?? '');
        $role = trim($input['role'] ?? 'citizen');
        $passwordHash = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);

        // Admin override prefix logic from a1
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6);
            $role = 'admin';
        }

        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, is_active) VALUES (:username, :email, :password_hash, :role, 1)');
        $stmt->execute([
            'username'      => $name,
            'email'         => trim($input['email'] ?? ''),
            'password_hash' => $passwordHash,
            'role'          => $role,
        ]);

        return self::getUserById((int)$pdo->lastInsertId());
    }

    public static function updateUserRecord(int $id, array $input): bool {
        $pdo = Database::getInstance()->getConnection();
        
        // Fetch current user to preserve values if missing in input
        $currentUser = self::getUserById($id);
        if (!$currentUser) return false;

        $name  = trim($input['name']  ?? $currentUser->getName());
        $email = trim($input['email'] ?? $currentUser->getEmail());
        $role  = trim($input['role']  ?? $currentUser->getRole());
        
        // Admin override prefix logic from a1
        if (strpos($name, 'admin-') === 0) {
            $name = substr($name, 6);
            $role = 'admin';
        }

        $twoFaEnabled = isset($input['two_fa_enabled']) ? (int)$input['two_fa_enabled'] : ($currentUser->isTwoFaEnabled() ? 1 : 0);

        $params = [
            'username' => empty($name) ? $currentUser->getDisplayName() : $name,
            'email'    => $email,
            'role'     => $role,
            'two_fa'   => $twoFaEnabled,
            'id'       => $id,
        ];

        $sql = 'UPDATE users SET username = :username, email = :email, role = :role, two_fa_enabled = :two_fa';
        
        if (!empty($input['password'])) {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function deleteUserRecord(int $id): bool {
        $pdo = Database::getInstance()->getConnection();
        // Ensure profile is cleaned up first to avoid foreign key violations
        $pdo->prepare('DELETE FROM profile WHERE user_id = :id')->execute(['id' => $id]);
        
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
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
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
        $stmt->bindParam(':pic', $blobData, \PDO::PARAM_LOB);
        $stmt->bindParam(':mime', $mime, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
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
        // Initialize with username as first_name
        $stmt = $pdo->prepare('INSERT INTO profile (user_id, first_name) SELECT id, username FROM users WHERE id = :user_id');
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
        
        $fields = [];
        $params = ['user_id' => $userId];

        $mapping = [
            'first_name' => 'first_name',
            'name'       => 'first_name',
            'last_name'  => 'last_name',
            'bio'        => 'bio',
            'phone_number' => 'phone_number',
            'phone'      => 'phone_number',
            'date_of_birth' => 'date_of_birth',
            'dob'        => 'date_of_birth',
            'avatar_url' => 'avatar_url',
            'avatar'     => 'avatar_url'
        ];

        foreach ($mapping as $inputKey => $dbCol) {
            if (isset($data[$inputKey])) {
                $val = $data[$inputKey];
                // Special handling for dates
                if ($dbCol === 'date_of_birth' && empty($val)) {
                    $val = null;
                }
                $fields[] = "$dbCol = :$inputKey";
                $params[$inputKey] = $val;
            }
        }

        if (empty($fields)) return true;

        $query = 'UPDATE profile SET ' . implode(', ', $fields) . ' WHERE user_id = :user_id';
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
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
            $errors['name'] = 'Full name is required.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Full name must be at least 3 characters long.';
        } elseif (preg_match('/\d/', $name)) {
            $errors['name'] = 'Full name cannot contain numeric characters.';
        } elseif (self::usernameExists($name, $userId)) {
            $errors['name'] = 'This username is already taken.';
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

        // Verify Internal Professional CAPTCHA
        $userCode = strtoupper(trim($input['captcha_code'] ?? ''));
        $serverCode = $_SESSION['captcha_code'] ?? '';
        
        if (empty($serverCode) || $userCode !== strtoupper($serverCode)) {
            return ['errors' => ['captcha' => 'Invalid security code. Please try again.']];
        }
        
        unset($_SESSION['captcha_code']); // Clear code after use

        $user = self::getUserByEmail(trim($input['email']));
        if ($user === null || $user->getPasswordHash() === null || !password_verify($input['password'], $user->getPasswordHash())) {
            return ['errors' => ['email' => 'Invalid email or password.']];
        }

        // Check if account is deactivated
        if (!$user->isActive()) {
            return ['errors' => ['email' => 'This account has been deactivated. Contact an administrator.']];
        }

        if ($user->isTwoFaEnabled()) {
            $_SESSION['pending_2fa_user_id'] = $user->getId();
            self::generateOtp($user->getId());
            return ['requires_2fa' => true];
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

        $isAdmin = (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin');
        
        // Ensure profile exists for this user (handles legacy accounts)
        self::ensureProfileExists($id);
        
        $errors = self::validateUser($input, false, $id, $isAdmin);
        
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $updated = self::updateUserRecord($id, $input);
        if (!$updated) {
            return ['errors' => ['general' => 'Unable to update user credentials.']];
        }

        // Profile updates
        self::ensureProfileExists($id);
        
        $profileData = [];
        // Map generic 'name' to profile 'first_name' if present
        if (isset($input['name']))          $profileData['name']         = $input['name'];
        if (isset($input['first_name']))    $profileData['first_name']   = $input['first_name'];
        if (isset($input['bio']))           $profileData['bio']          = $input['bio'];
        if (isset($input['phone_number']))  $profileData['phone_number'] = $input['phone_number'];
        if (isset($input['date_of_birth'])) $profileData['date_of_birth'] = $input['date_of_birth'];
        if (isset($input['avatar_url']))    $profileData['avatar_url']   = $input['avatar_url'];
        if (isset($input['avatar']))        $profileData['avatar_url']   = $input['avatar'];
        
        if (!empty($profileData)) {
            self::updateProfileRecord($id, $profileData);
        }

        // Handle profile picture upload if provided (from BackOffice form)
        $file = $input['avatar_file'] ?? $input['profile_pic_file'] ?? null;
        if (!$file && isset($_FILES['avatar'])) {
            $file = $_FILES['avatar'];
        }

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            self::uploadProfilePic($id, $file);
        }

        $refreshedUser = self::getUserById($id);

        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$id) {
            $_SESSION['user_name']  = $refreshedUser->getDisplayName();
            $_SESSION['user_email'] = $refreshedUser->getEmail();
        }

        return ['success' => 'Profile updated successfully.', 'user' => $refreshedUser];
    }

    public static function createUser(array $input): array {
        try {
            // SECURITY: $allowAdmin = true — this action is only reachable by admins
            $errors = self::validateUser($input, true, null, true);
            if (!empty($errors)) {
                return ['errors' => $errors];
            }

            $user = self::createUserRecord($input);
            self::ensureProfileExists($user->getId());
            
            // Also initialize profile with the provided name
            self::updateProfileRecord($user->getId(), ['first_name' => $input['name']]);

            // Handle profile picture upload if provided
            $file = $input['avatar'] ?? $input['avatar_file'] ?? $input['profile_pic_file'] ?? null;
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                self::uploadProfilePic($user->getId(), $file);
                $user = self::getUserById($user->getId()); // Refresh to include pic info
            }

            return ['success' => 'User created successfully.', 'user' => $user];
        } catch (\Exception $e) {
            error_log("[CivicPortal][UserController] Create user failed: " . $e->getMessage());
            return ['errors' => ['general' => 'Server error: ' . $e->getMessage()]];
        }
    }

    public static function deleteUser(int $id): array {
        if (!self::deleteUserRecord($id)) {
            return ['errors' => ['general' => 'Deletion failed.']];
        }

        return ['success' => 'User deleted successfully.'];
    }

    // =========================================================================
    // OTP & TWO-FACTOR AUTH
    // =========================================================================

    public static function generateOtp(int $userId): string {
        $pdo = Database::getInstance()->getConnection();
        $otp = (string)rand(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        
        $stmt = $pdo->prepare('UPDATE users SET otp_code = :otp, otp_expiry = :expiry WHERE id = :id');
        $stmt->execute(['otp' => $otp, 'expiry' => $expiry, 'id' => $userId]);
        
        $user = self::getUserById($userId);
        self::sendOtpEmail($user->getEmail(), $otp);
        
        return $otp;
    }

    private static function sendOtpEmail(string $email, string $otp): void {
        $mailConfig = require __DIR__ . '/../config/mail.php';
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $mailConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailConfig['username'];
            $mail->Password   = $mailConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $mailConfig['port'];

            $mail->setFrom($mailConfig['username'], $mailConfig['from_name']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre code de verification CivicPortal';
            $mail->Body    = "Votre code OTP est : <b>$otp</b>. Il expirera dans 5 minutes.";
            $mail->AltBody = "Votre code OTP est : $otp. Il expirera dans 5 minutes.";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
            $_SESSION['mail_error'] = "Mailer Error: " . $mail->ErrorInfo;
        }
    }

    public static function verifyOtp(int $userId, string $code): bool {
        $user = self::getUserById($userId);
        if (!$user || !$user->getOtpCode()) return false;
        
        if ($user->getOtpCode() !== $code) return false;
        
        if (strtotime($user->getOtpExpiry()) < time()) return false;
        
        // Code valid, clear it
        $pdo = Database::getInstance()->getConnection();
        $pdo->prepare('UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = :id')->execute(['id' => $userId]);
        
        return true;
    }

    // =========================================================================
    // PASSWORD RESET (EMAIL)
    // =========================================================================

    public static function requestPasswordReset(string $email): array {
        $user = self::getUserByEmail($email);
        if (!$user) {
            return ['errors' => ['email' => 'User not found.']];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id');
        $stmt->execute([
            'token' => $token,
            'expires' => $expires,
            'id' => $user->getId()
        ]);

        if (self::sendPasswordResetEmail($user, $token)) {
            return ['success' => 'Reset link has been sent to your email.'];
        } else {
            return ['errors' => ['email' => 'Failed to send email.']];
        }
    }

    private static function sendPasswordResetEmail($user, $token): bool {
        $mailConfig = require __DIR__ . '/../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $mailConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailConfig['username'];
            $mail->Password   = $mailConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $mailConfig['port'];

            $mail->setFrom($mailConfig['username'], $mailConfig['from_name']);
            $mail->addAddress($user->getEmail());

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/integ/View/FrontOffice/reset_password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your CivicPortal Password';
            $mail->Body    = "
                <div style='font-family: sans-serif; padding: 20px; color: #1D2A44;'>
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user->getDisplayName()},</p>
                    <p>You requested to reset your password. Click the button below to proceed:</p>
                    <a href='{$resetLink}' style='display: inline-block; padding: 12px 24px; background: #1D2A44; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Reset Password</a>
                    <p>If you did not request this, please ignore this email.</p>
                    <p>This link will expire in 1 hour.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function resetPassword(string $token, string $password, string $confirmPassword): array {
        if ($password !== $confirmPassword) {
            return ['errors' => ['confirm_password' => 'Passwords do not match.']];
        }
        if (strlen($password) < 8) {
            return ['errors' => ['password' => 'Password must be at least 8 characters.']];
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['errors' => ['password' => 'Invalid or expired token.']];
        }

        $userId = $row['id'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash, reset_token = NULL, reset_expires = NULL WHERE id = :id');
        $stmt->execute([
            'hash' => $hash,
            'id' => $userId
        ]);

        return ['success' => 'Password updated successfully. You can now log in.'];
    }
}
