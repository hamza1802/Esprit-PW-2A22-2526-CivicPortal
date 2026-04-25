<?php
/**
 * Trajet.php — Model/Trajet.php
 * Blueprint entity for a transport Route (Trajet).
 * Ported from transport-ui branch; no logic changes.
 */

class Trajet {
    private ?int    $idTrajet;
    private ?string $departure;
    private ?string $destination;
    private ?int    $idTransport;
    private ?string $departureTime;
    private ?float  $price;
    private ?float  $depLat;
    private ?float  $depLng;
    private ?string $depAddress;
    private ?float  $destLat;
    private ?float  $destLng;
    private ?string $destAddress;

    public function __construct(
        ?int    $idTrajet,
        ?string $departure,
        ?string $destination,
        ?int    $idTransport,
        ?string $departureTime,
        ?float  $price,
        ?float  $depLat    = null,
        ?float  $depLng    = null,
        ?string $depAddress = null,
        ?float  $destLat   = null,
        ?float  $destLng   = null,
        ?string $destAddress = null
    ) {
        $this->idTrajet      = $idTrajet;
        $this->departure     = $departure;
        $this->destination   = $destination;
        $this->idTransport   = $idTransport;
        $this->departureTime = $departureTime;
        $this->price         = $price;
        $this->depLat        = $depLat;
        $this->depLng        = $depLng;
        $this->depAddress    = $depAddress;
        $this->destLat       = $destLat;
        $this->destLng       = $destLng;
        $this->destAddress   = $destAddress;
    }

    // --- Getters ---
    public function getIdTrajet(): ?int       { return $this->idTrajet; }
    public function getDeparture(): ?string   { return $this->departure; }
    public function getDestination(): ?string { return $this->destination; }
    public function getIdTransport(): ?int    { return $this->idTransport; }
    public function getDepartureTime(): ?string { return $this->departureTime; }
    public function getPrice(): ?float        { return $this->price; }
    public function getDepLat(): ?float       { return $this->depLat; }
    public function getDepLng(): ?float       { return $this->depLng; }
    public function getDepAddress(): ?string  { return $this->depAddress; }
    public function getDestLat(): ?float      { return $this->destLat; }
    public function getDestLng(): ?float      { return $this->destLng; }
    public function getDestAddress(): ?string { return $this->destAddress; }

    // --- Setters ---
    public function setDeparture(string $d): void     { $this->departure = $d; }
    public function setDestination(string $d): void   { $this->destination = $d; }
    public function setIdTransport(int $id): void     { $this->idTransport = $id; }
    public function setDepartureTime(string $t): void { $this->departureTime = $t; }
    public function setPrice(float $p): void           { $this->price = $p; }
    public function setDepLat(?float $v): void         { $this->depLat = $v; }
    public function setDepLng(?float $v): void         { $this->depLng = $v; }
    public function setDepAddress(?string $v): void    { $this->depAddress = $v; }
    public function setDestLat(?float $v): void        { $this->destLat = $v; }
    public function setDestLng(?float $v): void        { $this->destLng = $v; }
    public function setDestAddress(?string $v): void   { $this->destAddress = $v; }
}
?>
