<?php
/**
 * AppointmentSlot.php — Model/AppointmentSlot.php
 * Entity representing an availability window defined by admin.
 */

class AppointmentSlot implements JsonSerializable {
    private ?int $id;
    private int $agentId;
    private string $serviceType;
    private int $dayOfWeek; // 0=Sun, 1=Mon, ..., 6=Sat
    private string $startTime;
    private string $endTime;
    private bool $isActive;
    private ?string $createdAt;
    private ?string $agentName;

    public function __construct(
        ?int $id, int $agentId, string $serviceType, int $dayOfWeek,
        string $startTime, string $endTime, bool $isActive = true,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->agentId = $agentId;
        $this->serviceType = $serviceType;
        $this->dayOfWeek = $dayOfWeek;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getAgentId(): int { return $this->agentId; }
    public function getServiceType(): string { return $this->serviceType; }
    public function getDayOfWeek(): int { return $this->dayOfWeek; }
    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): string { return $this->endTime; }
    public function isActive(): bool { return $this->isActive; }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    public function toArray(): array {
        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return [
            'id' => $this->id,
            'agent_id' => $this->agentId,
            'service_type' => $this->serviceType,
            'day_of_week' => $this->dayOfWeek,
            'day_name' => $days[$this->dayOfWeek] ?? '',
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'is_active' => $this->isActive,
            'agent_name' => $this->agentName ?? null,
        ];
    }

    public static function fromRow(array $row): self {
        $s = new self(
            (int)$row['id'],
            (int)$row['agent_id'],
            $row['service_type'],
            (int)$row['day_of_week'],
            $row['start_time'],
            $row['end_time'],
            (bool)($row['is_active'] ?? true),
            $row['created_at'] ?? null
        );
        $s->agentName = $row['agent_name'] ?? $row['username'] ?? null;
        return $s;
    }
}
