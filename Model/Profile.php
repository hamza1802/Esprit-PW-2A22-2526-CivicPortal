<?php
/**
 * Profile.php — Model/Profile.php
 * Entity representing user profile information.
 * Updated: avatar_url removed (profile pics now stored as BLOB on users table).
 */

class Profile {
    private int $userId;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $bio;
    private ?string $phoneNumber;
    private ?string $dateOfBirth;

    public function __construct(
        int $userId, ?string $firstName = null, ?string $lastName = null,
        ?string $bio = null, ?string $phoneNumber = null, ?string $dateOfBirth = null
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->bio = $bio;
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
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function getDateOfBirth(): ?string { return $this->dateOfBirth; }

    public static function fromRow(array $row): Profile {
        return new Profile(
            (int)$row['user_id'], 
            $row['first_name'] ?? null,
            $row['last_name'] ?? null,
            $row['bio'] ?? null,
            $row['phone_number'] ?? null,
            $row['date_of_birth'] ?? null
        );
    }
}
