<?php
/**
 * Appointment.php — Model/Appointment.php
 * Entity representing a citizen appointment booking.
 */

class Appointment implements JsonSerializable {
    private ?int $id;
    private int $userId;
    private string $serviceType;
    private string $preferredDate;
    private string $preferredTime;
    private ?string $notes;
    private string $status;
    private ?int $assignedTo;
    private ?string $rescheduleReason;
    private ?string $newDate;
    private ?string $newTime;
    private ?string $createdAt;
    private ?string $updatedAt;
    // Joined fields
    private ?string $userName;
    private ?string $agentName;

    public function __construct(
        ?int $id, int $userId, string $serviceType, string $preferredDate,
        string $preferredTime, ?string $notes = null, string $status = 'pending',
        ?int $assignedTo = null, ?string $rescheduleReason = null,
        ?string $newDate = null, ?string $newTime = null,
        ?string $createdAt = null, ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->serviceType = $serviceType;
        $this->preferredDate = $preferredDate;
        $this->preferredTime = $preferredTime;
        $this->notes = $notes;
        $this->status = $status;
        $this->assignedTo = $assignedTo;
        $this->rescheduleReason = $rescheduleReason;
        $this->newDate = $newDate;
        $this->newTime = $newTime;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // --- Getters ---
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getServiceType(): string { return $this->serviceType; }
    public function getPreferredDate(): string { return $this->preferredDate; }
    public function getPreferredTime(): string { return $this->preferredTime; }
    public function getNotes(): ?string { return $this->notes; }
    public function getStatus(): string { return $this->status; }
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function getRescheduleReason(): ?string { return $this->rescheduleReason; }
    public function getNewDate(): ?string { return $this->newDate; }
    public function getNewTime(): ?string { return $this->newTime; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUserName(): ?string { return $this->userName ?? null; }
    public function getAgentName(): ?string { return $this->agentName ?? null; }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'service_type' => $this->serviceType,
            'preferred_date' => $this->preferredDate,
            'preferred_time' => $this->preferredTime,
            'notes' => $this->notes,
            'status' => $this->status,
            'assigned_to' => $this->assignedTo,
            'reschedule_reason' => $this->rescheduleReason,
            'new_date' => $this->newDate,
            'new_time' => $this->newTime,
            'created_at' => $this->createdAt,
            'user_name' => $this->userName ?? null,
            'agent_name' => $this->agentName ?? null,
        ];
    }

    public static function fromRow(array $row): self {
        $a = new self(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['service_type'],
            $row['preferred_date'],
            $row['preferred_time'],
            $row['notes'] ?? null,
            $row['status'] ?? 'pending',
            isset($row['assigned_to']) ? (int)$row['assigned_to'] : null,
            $row['reschedule_reason'] ?? null,
            $row['new_date'] ?? null,
            $row['new_time'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
        $a->userName = $row['user_name'] ?? $row['username'] ?? null;
        $a->agentName = $row['agent_name'] ?? null;
        return $a;
    }
}
