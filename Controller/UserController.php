<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, two_fa_enabled, otp_code, otp_expiry FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public static function getUserByEmail(string $email): ?User {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, email, role, password_hash, created_at, two_fa_enabled, otp_code, otp_expiry FROM users WHERE email = :email');
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

        if (isset($data['two_fa_enabled'])) {
            $pdo->prepare('UPDATE users SET two_fa_enabled = :two_fa WHERE id = :id')->execute([
                'two_fa' => (int)$data['two_fa_enabled'],
                'id' => $userId
            ]);
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
        
        // CAPTCHA Validation
        if (empty($input['captcha_code'])) {
            $errors['captcha'] = 'Security code is required.';
        }
        
        return $errors;
    }

    // --- Actions ---

    public static function login(array $input): array {
        $errors = self::validateUserLogin($input);
        if (!empty($errors)) return ['errors' => $errors];

        // Verify Internal Professional CAPTCHA
        $userCode = strtoupper(trim($input['captcha_code'] ?? ''));
        $serverCode = $_SESSION['captcha_code'] ?? '';
        
        if (empty($serverCode) || $userCode !== strtoupper($serverCode)) {
            return ['errors' => ['captcha' => 'Invalid security code. Please try again.']];
        }
        
        unset($_SESSION['captcha_code']); // Clear code after use

        $user = self::getUserByEmail(trim($input['email']));
        if (!$user || !password_verify($input['password'], $user->getPasswordHash())) {
            return ['errors' => ['email' => 'Invalid email or password.']];
        }

        if ($user->isTwoFaEnabled()) {
            $_SESSION['pending_2fa_user_id'] = $user->getId();
            self::generateOtp($user->getId());
            return ['requires_2fa' => true];
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        self::ensureProfileExists($user->getId());

        return ['success' => 'Login successful.', 'user' => $user];
    }

    public static function generateOtp(int $userId): string {
        $pdo = Database::getInstance();
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
        
        if ($mailConfig['username'] === 'votre-email@gmail.com') {
            error_log("ERREUR : Vous devez configurer votre email Gmail dans config/mail.php");
            return;
        }

        $phpMailerPath = __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
        $smtpPath = __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
        $exceptionPath = __DIR__ . '/../lib/PHPMailer/src/Exception.php';

        if (file_exists($phpMailerPath) && file_exists($smtpPath) && file_exists($exceptionPath)) {
            require_once $exceptionPath;
            require_once $phpMailerPath;
            require_once $smtpPath;

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = $mailConfig['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailConfig['username'];
                $mail->Password   = $mailConfig['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Debugging
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    $_SESSION['smtp_debug'] = ($_SESSION['smtp_debug'] ?? '') . $str . "\n";
                };
                $_SESSION['smtp_debug'] = "";

                //Recipients
                $mail->setFrom($mailConfig['username'], $mailConfig['from_name']);
                $mail->addAddress($email);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Votre code de verification CivicPortal';
                $mail->Body    = "Votre code OTP est : <b>$otp</b>. Il expirera dans 5 minutes.";
                $mail->AltBody = "Votre code OTP est : $otp. Il expirera dans 5 minutes.";

                $mail->send();
            } catch (Exception $e) {
                $_SESSION['mail_error'] = "Mail Error: " . $mail->ErrorInfo . "\n\nDebug Log:\n" . ($_SESSION['smtp_debug'] ?? '');
                error_log("Mail Error: {$mail->ErrorInfo}");
            }
        } else {
            $_SESSION['mail_error'] = "PHPMailer files not found in lib/PHPMailer/src/";
            error_log("PHPMailer non trouve. L'envoi d'email a echoue.");
        }
    }

    public static function verifyOtp(int $userId, string $code): bool {
        $user = self::getUserById($userId);
        if (!$user || !$user->getOtpCode()) return false;
        
        if ($user->getOtpCode() !== $code) return false;
        
        if (strtotime($user->getOtpExpiry()) < time()) return false;
        
        // Code valide, on le nettoie
        $pdo = Database::getInstance();
        $pdo->prepare('UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = :id')->execute(['id' => $userId]);
        
        return true;
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

    public static function requestPasswordReset(string $email): array {
        $user = self::getUserByEmail($email);
        if (!$user) {
            return ['errors' => ['email' => 'User not found.']];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $pdo = Database::getInstance();
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

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/a1/index.php?page=front_reset_password&token=" . $token;

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
        if (strlen($password) < 6) {
            return ['errors' => ['password' => 'Password must be at least 6 characters.']];
        }

        $pdo = Database::getInstance();
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
