<?php
/**
 * User.php — Model/User.php
 * Entité représentant un utilisateur du CivicPortal.
 */

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
}


