<?php
/**
 * ServiceRequest.php — Model/ServiceRequest.php
 * Blueprint entity for a municipal service request.
 * Maps to `requests` table: id, user_id, title, description, status, created_at, updated_at
 */

class ServiceRequest {
    private ?int   $id;
    private string $title;
    private string $description;
    private int    $userId;
    private string $status;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(?int $id, string $title, string $description, int $userId, string $status = 'pending', string $createdAt = '', string $updatedAt = '') {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->userId      = $userId;
        $this->status      = $status;
        $this->createdAt   = $createdAt ?: date('Y-m-d H:i:s');
        $this->updatedAt   = $updatedAt ?: date('Y-m-d H:i:s');
    }

    // --- Getters ---
    public function getId(): ?int       { return $this->id; }
    public function getTitle(): string  { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getUserId(): int    { return $this->userId; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // --- Setters ---
    public function setTitle(string $title)               { $this->title = $title; }
    public function setDescription(string $description)   { $this->description = $description; }
    public function setStatus(string $status)              { $this->status = $status; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'userId'      => $this->userId,
            'status'      => $this->status,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt
        ];
    }
}
?>
