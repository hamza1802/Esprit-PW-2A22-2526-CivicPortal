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
    private ?string $start_date;
    private ?string $end_date;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        string $category,
        int $capacity,
        string $location,
        string $status = 'active',
        ?string $image = 'default.jpg',
        ?string $start_date = null,
        ?string $end_date = null
    ) {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->category    = $category;
        $this->capacity    = $capacity;
        $this->location    = $location;
        $this->status      = $status;
        $this->image       = $image;
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
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
    public function getStartDate()   { return $this->start_date; }
    public function getEndDate()     { return $this->end_date; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'category'    => $this->category,
            'capacity'    => $this->capacity,
            'location'    => $this->location,
            'status'      => $this->status,
            'image'       => $this->image,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date
        ];
    }
}
?>
