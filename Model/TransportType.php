<?php
/**
 * TransportType.php — Model/TransportType.php
 * Blueprint entity for a Transport Type (Bus, Train, etc.).
 * Ported from transport-ui branch; no logic changes.
 */

class TransportType {
    private ?int    $idTransportType;
    private ?string $name;
    private ?string $description;
    private ?string $photo_url;

    public function __construct(
        ?int    $idTransportType,
        ?string $name,
        ?string $description,
        ?string $photo_url
    ) {
        $this->idTransportType = $idTransportType;
        $this->name            = $name;
        $this->description     = $description;
        $this->photo_url       = $photo_url;
    }

    // --- Getters ---
    public function getIdTransportType(): ?int { return $this->idTransportType; }
    public function getName(): ?string         { return $this->name; }
    public function getDescription(): ?string  { return $this->description; }
    public function getPhotoUrl(): ?string     { return $this->photo_url; }

    // --- Setters ---
    public function setName(string $name): void             { $this->name = $name; }
    public function setDescription(string $desc): void      { $this->description = $desc; }
    public function setPhotoUrl(string $photo_url): void    { $this->photo_url = $photo_url; }
}
?>
