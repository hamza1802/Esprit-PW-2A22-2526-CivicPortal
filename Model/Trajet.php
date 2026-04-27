<?php
class Trajet {
    private ?int $idTrajet;
    private ?string $departure;
    private ?string $destination;
    private ?int $idTransport;
    private ?string $departureTime;
    private ?float $price;
    private ?float $depLat;
    private ?float $depLng;
    private ?float $destLat;
    private ?float $destLng;

    public function __construct(
        ?int $idTrajet, ?string $departure, ?string $destination,
        ?int $idTransport, ?string $departureTime, ?float $price,
        ?float $depLat = null, ?float $depLng = null,
        ?float $destLat = null, ?float $destLng = null
    ) {
        $this->idTrajet = $idTrajet;
        $this->departure = $departure;
        $this->destination = $destination;
        $this->idTransport = $idTransport;
        $this->departureTime = $departureTime;
        $this->price = $price;
        $this->depLat = $depLat;
        $this->depLng = $depLng;
        $this->destLat = $destLat;
        $this->destLng = $destLng;
    }

    // Getters
    public function getIdTrajet(): ?int { return $this->idTrajet; }
    public function getDeparture(): ?string { return $this->departure; }
    public function getDestination(): ?string { return $this->destination; }
    public function getIdTransport(): ?int { return $this->idTransport; }
    public function getDepartureTime(): ?string { return $this->departureTime; }
    public function getPrice(): ?float { return $this->price; }
    public function getDepLat(): ?float { return $this->depLat; }
    public function getDepLng(): ?float { return $this->depLng; }
    public function getDestLat(): ?float { return $this->destLat; }
    public function getDestLng(): ?float { return $this->destLng; }

    // Setters
    public function setDeparture(string $departure): void { $this->departure = $departure; }
    public function setDestination(string $destination): void { $this->destination = $destination; }
    public function setIdTransport(int $idTransport): void { $this->idTransport = $idTransport; }
    public function setDepartureTime(string $departureTime): void { $this->departureTime = $departureTime; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function setDepLat(?float $depLat): void { $this->depLat = $depLat; }
    public function setDepLng(?float $depLng): void { $this->depLng = $depLng; }
    public function setDestLat(?float $destLat): void { $this->destLat = $destLat; }
    public function setDestLng(?float $destLng): void { $this->destLng = $destLng; }
}
?>
