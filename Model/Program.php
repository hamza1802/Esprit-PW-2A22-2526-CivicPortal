<?php
/**
 * Program.php — Model/Program.php
 * Blueprint entity for a Community Program (Parks & Rec).
 */

class Program {
    private ?int   $id;
    private string $title;
    private string $description;
    private string $category;
    private int    $capacity;
    private string $location;
    private string $status;
    private ?string $image;

    public function __construct(?int $id, string $title, string $description, string $category, int $capacity, string $location, string $status = 'active', ?string $image = 'default.jpg') {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->category    = $category;
        $this->capacity    = $capacity;
        $this->location    = $location;
        $this->status      = $status;
        $this->image       = $image;
    }

    // --- Getters ---
    public function getId()          { return $this->id; }
    public function getTitle()       { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getCategory()    { return $this->category; }
    public function getCapacity()    { return $this->capacity; }
    public function getLocation()    { return $this->location; }
    public function getStatus()      { return $this->status; }
    public function getImage()       { return $this->image; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'category'    => $this->category,
            'capacity'    => $this->capacity,
            'location'    => $this->location,
            'status'      => $this->status,
            'image'       => $this->image
        ];
    }
}
?>
