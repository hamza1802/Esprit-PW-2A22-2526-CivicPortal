<?php
class Ticket {
    private ?int $idTicket;
    private ?int $idUser;
    private ?string $ref;
    private ?string $citizenName;
    private ?int $idTrajet;
    private ?string $issuedAt;
    private ?string $status;

    public function __construct(?int $idTicket, ?int $idUser, ?string $ref, ?string $citizenName, ?int $idTrajet, ?string $issuedAt, ?string $status) {
        $this->idTicket = $idTicket;
        $this->idUser = $idUser;
        $this->ref = $ref;
        $this->citizenName = $citizenName;
        $this->idTrajet = $idTrajet;
        $this->issuedAt = $issuedAt;
        $this->status = $status;
    }

    // Getters
    public function getIdTicket(): ?int { return $this->idTicket; }
    public function getIdUser(): ?int { return $this->idUser; }
    public function getRef(): ?string { return $this->ref; }
    public function getCitizenName(): ?string { return $this->citizenName; }
    public function getIdTrajet(): ?int { return $this->idTrajet; }
    public function getIssuedAt(): ?string { return $this->issuedAt; }
    public function getStatus(): ?string { return $this->status; }

    // Setters
    public function setIdUser(?int $idUser): void { $this->idUser = $idUser; }
    public function setRef(string $ref): void { $this->ref = $ref; }
    public function setCitizenName(string $citizenName): void { $this->citizenName = $citizenName; }
    public function setIdTrajet(int $idTrajet): void { $this->idTrajet = $idTrajet; }
    public function setStatus(string $status): void { $this->status = $status; }
}
?>
