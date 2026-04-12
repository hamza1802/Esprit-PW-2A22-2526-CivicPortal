<?php
/**
 * Document.php — Model/Document.php
 * Blueprint entity for a document attached to a ServiceRequest.
 * Maps to `documents` table: id, request_id, file_path, type, uploaded_at
 */

class Document {
    private ?int   $id;
    private int    $requestId;
    private string $filePath;
    private string $type;       // enum: identity, proof, photo, certificate, other
    private string $uploadedAt;

    public function __construct(?int $id, int $requestId, string $filePath, string $type = 'other', string $uploadedAt = '') {
        $this->id         = $id;
        $this->requestId  = $requestId;
        $this->filePath   = $filePath;
        $this->type       = $type;
        $this->uploadedAt = $uploadedAt ?: date('Y-m-d H:i:s');
    }

    // --- Getters ---
    public function getId(): ?int         { return $this->id; }
    public function getRequestId(): int   { return $this->requestId; }
    public function getFilePath(): string { return $this->filePath; }
    public function getType(): string     { return $this->type; }
    public function getUploadedAt(): string { return $this->uploadedAt; }

    // --- Setters ---
    public function setFilePath(string $filePath) { $this->filePath = $filePath; }
    public function setType(string $type)         { $this->type = $type; }

    public function toArray(): array {
        return [
            'id'         => $this->id,
            'requestId'  => $this->requestId,
            'filePath'   => $this->filePath,
            'type'       => $this->type,
            'uploadedAt' => $this->uploadedAt
        ];
    }
}
?>
