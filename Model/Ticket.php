<?php
/**
 * Ticket.php — Model/Ticket.php
 * Blueprint entity for a transport Ticket.
 * Ported from transport-ui branch.
 * Note: DB column is `user_id` (integration schema), PHP getter is getIdUser().
 */

class Ticket {
    private ?int    $idTicket;
    private ?int    $idUser;       // maps to DB column: user_id
    private ?string $ref;
    private ?string $citizenName;
    private ?int    $idTrajet;
    private ?string $issuedAt;
    private ?string $status;

    public function __construct(
        ?int    $idTicket,
        ?int    $idUser,
        ?string $ref,
        ?string $citizenName,
        ?int    $idTrajet,
        ?string $issuedAt,
        ?string $status
    ) {
        $this->idTicket     = $idTicket;
        $this->idUser       = $idUser;
        $this->ref          = $ref;
        $this->citizenName  = $citizenName;
        $this->idTrajet     = $idTrajet;
        $this->issuedAt     = $issuedAt;
        $this->status       = $status;
    }

    // --- Getters ---
    public function getIdTicket(): ?int     { return $this->idTicket; }
    public function getIdUser(): ?int       { return $this->idUser; }
    public function getRef(): ?string       { return $this->ref; }
    public function getCitizenName(): ?string { return $this->citizenName; }
    public function getIdTrajet(): ?int     { return $this->idTrajet; }
    public function getIssuedAt(): ?string  { return $this->issuedAt; }
    public function getStatus(): ?string    { return $this->status; }

    // --- Setters ---
    public function setIdUser(?int $id): void         { $this->idUser = $id; }
    public function setRef(string $ref): void          { $this->ref = $ref; }
    public function setCitizenName(string $n): void    { $this->citizenName = $n; }
    public function setIdTrajet(int $id): void         { $this->idTrajet = $id; }
    public function setStatus(string $status): void    { $this->status = $status; }
}
?>
