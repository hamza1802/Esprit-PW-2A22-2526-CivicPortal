<?php
class TransportType {
    private ?int $idTransportType;
    private ?string $name;
    private ?string $description;
    private ?string $image_blob;
    private ?string $image_mime;

    public function __construct(?int $idTransportType, ?string $name, ?string $description, ?string $image_blob = null, ?string $image_mime = null) {
        $this->idTransportType = $idTransportType;
        $this->name = $name;
        $this->description = $description;
        $this->image_blob = $image_blob;
        $this->image_mime = $image_mime;
    }

    // Getters
    public function getIdTransportType(): ?int { return $this->idTransportType; }
    public function getName(): ?string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getImageBlob(): ?string { return $this->image_blob; }
    public function getImageMime(): ?string { return $this->image_mime; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setImageBlob(string $image_blob): void { $this->image_blob = $image_blob; }
    public function setImageMime(string $image_mime): void { $this->image_mime = $image_mime; }
}
?>
