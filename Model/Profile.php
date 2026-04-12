<?php
/**
 * Profile.php — Model/Profile.php
 * Gestion PDO des informations de profil utilisateur.
 */

require_once __DIR__ . '/../config/database.php';

class Profile {
    private int $userId;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $bio;
    private ?string $avatarUrl;
    private ?string $phoneNumber;
    private ?string $dateOfBirth;

    public function __construct(int $userId, ?string $firstName = null, ?string $lastName = null, ?string $bio = null, ?string $avatarUrl = null, ?string $phoneNumber = null, ?string $dateOfBirth = null) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->bio = $bio;
        $this->avatarUrl = $avatarUrl;
        $this->phoneNumber = $phoneNumber;
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getUserId(): int { return $this->userId; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getFullName(): string {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? '')) ?: '';
    }
    public function getBio(): ?string { return $this->bio; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function getDateOfBirth(): ?string { return $this->dateOfBirth; }

    public static function fromRow(array $row): Profile {
        return new Profile(
            (int)$row['user_id'], 
            $row['first_name'] ?? null,
            $row['last_name'] ?? null,
            $row['bio'] ?? null,
            $row['avatar_url'] ?? null,
            $row['phone_number'] ?? null,
            $row['date_of_birth'] ?? null
        );
    }

    public static function findByUserId(int $userId): ?Profile {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function create(int $userId): Profile {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO profile (user_id) VALUES (:user_id)');
        $stmt->execute(['user_id' => $userId]);
        return self::findByUserId($userId);
    }

    public static function createIfMissing(int $userId): Profile {
        $profile = self::findByUserId($userId);
        return $profile ?? self::create($userId);
    }

    public static function update(int $userId, array $data): bool {
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
}
