<?php
/**
 * User.php — Model/User.php
 * Entity representing a CivicPortal user.
 * Updated: is_active flag, profile pic helpers.
 */

class User implements JsonSerializable {
    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    private int $id;
    private string $name;
    private string $email;
    private string $role;
    private ?string $passwordHash;
    private ?string $createdAt;
    private bool $isActive;
    private bool $hasProfilePic;

    public function __construct(
        int $id, string $name, string $email, string $role = 'citizen',
        ?string $passwordHash = null, ?string $createdAt = null,
        bool $isActive = true, bool $hasProfilePic = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
        $this->isActive = $isActive;
        $this->hasProfilePic = $hasProfilePic;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function isActive(): bool { return $this->isActive; }
    public function hasProfilePic(): bool { return $this->hasProfilePic; }

    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setActive(bool $active): void { $this->isActive = $active; }

    // Get display name
    public function getDisplayName(): string {
        return $this->name;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt,
            'is_active' => $this->isActive,
            'has_profile_pic' => $this->hasProfilePic,
        ];
    }

    public static function fromRow(array $row): User {
        return new User(
            (int)$row['id'], 
            $row['username'] ?? $row['name'] ?? '',
            $row['email'] ?? '',
            $row['role'] ?? 'citizen',
            $row['password_hash'] ?? null,
            $row['created_at'] ?? null,
            isset($row['is_active']) ? (bool)$row['is_active'] : true,
            !empty($row['profile_pic'])
        );
    }
}
