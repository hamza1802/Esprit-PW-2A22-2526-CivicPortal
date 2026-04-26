<?php
/**
 * Notification.php — Model/Notification.php
 * Email-style notification log stored in DB (no actual email sending).
 */

class Notification implements JsonSerializable {
    private ?int $id;
    private int $userId;
    private string $title;
    private ?string $body;
    private string $type;
    private bool $isRead;
    private ?string $createdAt;

    public function __construct(
        ?int $id, int $userId, string $title, ?string $body = null,
        string $type = 'info', bool $isRead = false, ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->isRead = $isRead;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getBody(): ?string { return $this->body; }
    public function getType(): string { return $this->type; }
    public function isRead(): bool { return $this->isRead; }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromRow(array $row): self {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['title'],
            $row['body'] ?? null,
            $row['type'] ?? 'info',
            (bool)($row['is_read'] ?? false),
            $row['created_at'] ?? null
        );
    }
}
