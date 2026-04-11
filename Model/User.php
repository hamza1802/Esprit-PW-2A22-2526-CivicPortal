<?php
/**
 * User.php — Model/User.php
 * Blueprint entity for a CivicPortal User.
 */

class User {
    private int    $id;
    private string $name;
    private string $role;
    private string $email;

    public function __construct(int $id, string $name, string $role, string $email) {
        $this->id    = $id;
        $this->name  = $name;
        $this->role  = $role;
        $this->email = $email;
    }

    // --- Getters ---
    public function getId()    { return $this->id; }
    public function getName()  { return $this->name; }
    public function getRole()  { return $this->role; }
    public function getEmail() { return $this->email; }

    // --- Setters ---
    public function setName(string $name)   { $this->name  = $name; }
    public function setRole(string $role)   { $this->role  = $role; }
    public function setEmail(string $email) { $this->email = $email; }

    public function toArray(): array {
        return ['id' => $this->id, 'name' => $this->name, 'role' => $this->role, 'email' => $this->email];
    }
}
?>
