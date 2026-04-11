<?php
/**
 * ServiceRequest.php — Model/ServiceRequest.php
 * Blueprint entity for a municipal service request.
 */

class ServiceRequest {
    private int    $id;
    private string $type;
    private int    $userId;
    private string $status;
    private string $date;

    public function __construct(int $id, string $type, int $userId, string $status, string $date) {
        $this->id     = $id;
        $this->type   = $type;
        $this->userId = $userId;
        $this->status = $status;
        $this->date   = $date;
    }

    // --- Getters ---
    public function getId()     { return $this->id; }
    public function getType()   { return $this->type; }
    public function getUserId() { return $this->userId; }
    public function getStatus() { return $this->status; }
    public function getDate()   { return $this->date; }

    // --- Setters ---
    public function setStatus(string $status) { $this->status = $status; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'userId' => $this->userId,
            'status' => $this->status,
            'date' => $this->date
        ];
    }
}
?>
