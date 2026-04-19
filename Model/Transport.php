<?php
class Transport {
    private ?int $idTransport;
    private ?string $name;
    private ?string $type;
    private ?int $capacity;
    private ?string $status;
    private ?int $idTransportType;

    public function __construct(?int $idTransport, ?string $name, ?string $type, ?int $capacity, ?string $status, ?int $idTransportType = null) {
        $this->idTransport = $idTransport;
        $this->name = $name;
        $this->type = $type;
        $this->capacity = $capacity;
        $this->status = $status;
        $this->idTransportType = $idTransportType;
    }

    // Getters
    public function getIdTransport(): ?int { return $this->idTransport; }
    public function getName(): ?string { return $this->name; }
    public function getType(): ?string { return $this->type; }
    public function getCapacity(): ?int { return $this->capacity; }
    public function getStatus(): ?string { return $this->status; }
    public function getIdTransportType(): ?int { return $this->idTransportType; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setType(string $type): void { $this->type = $type; }
    public function setCapacity(int $capacity): void { $this->capacity = $capacity; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setIdTransportType(?int $idTransportType): void { $this->idTransportType = $idTransportType; }
}
?>
