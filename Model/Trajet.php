<?php
class Trajet {
    private ?int $idTrajet;
    private ?string $departure;
    private ?string $destination;
    private ?int $idTransport;
    private ?string $departureTime;
    private ?float $price;

    public function __construct(?int $idTrajet, ?string $departure, ?string $destination, ?int $idTransport, ?string $departureTime, ?float $price) {
        $this->idTrajet = $idTrajet;
        $this->departure = $departure;
        $this->destination = $destination;
        $this->idTransport = $idTransport;
        $this->departureTime = $departureTime;
        $this->price = $price;
    }

    // Getters
    public function getIdTrajet(): ?int { return $this->idTrajet; }
    public function getDeparture(): ?string { return $this->departure; }
    public function getDestination(): ?string { return $this->destination; }
    public function getIdTransport(): ?int { return $this->idTransport; }
    public function getDepartureTime(): ?string { return $this->departureTime; }
    public function getPrice(): ?float { return $this->price; }

    // Setters
    public function setDeparture(string $departure): void { $this->departure = $departure; }
    public function setDestination(string $destination): void { $this->destination = $destination; }
    public function setIdTransport(int $idTransport): void { $this->idTransport = $idTransport; }
    public function setDepartureTime(string $departureTime): void { $this->departureTime = $departureTime; }
    public function setPrice(float $price): void { $this->price = $price; }
}
?>
