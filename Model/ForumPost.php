<?php
/**
 * ForumPost.php — Model/ForumPost.php
 * Blueprint entity for a Forum Post.
 */

class ForumPost {
    private ?int    $post_id;
    private int     $user_id;
    private string  $title;
    private string  $content;
    private string  $category;
    private string  $status;
    private ?string $created_at;

    public function __construct(
        ?int    $post_id    = null,
        int     $user_id    = 0,
        string  $title      = '',
        string  $content    = '',
        string  $category   = 'Infrastructure',
        string  $status     = 'open',
        ?string $created_at = null
    ) {
        $this->post_id    = $post_id;
        $this->user_id    = $user_id;
        $this->title      = $title;
        $this->content    = $content;
        $this->category   = $category;
        $this->status     = $status;
        $this->created_at = $created_at;
    }

    // --- Getters ---
    public function getPostId(): ?int     { return $this->post_id; }
    public function getUserId(): int       { return $this->user_id; }
    public function getTitle(): string     { return $this->title; }
    public function getContent(): string   { return $this->content; }
    public function getCategory(): string  { return $this->category; }
    public function getStatus(): string    { return $this->status; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    // --- Setters ---
    public function setPostId(int $id): void        { $this->post_id = $id; }
    public function setUserId(int $id): void         { $this->user_id = $id; }
    public function setTitle(string $title): void    { $this->title = $title; }
    public function setContent(string $content): void { $this->content = $content; }
    public function setCategory(string $cat): void   { $this->category = $cat; }
    public function setStatus(string $status): void  { $this->status = $status; }
    public function setCreatedAt(string $dt): void   { $this->created_at = $dt; }

    public function toArray(): array {
        return [
            'post_id'    => $this->post_id,
            'user_id'    => $this->user_id,
            'title'      => $this->title,
            'content'    => $this->content,
            'category'   => $this->category,
            'status'     => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
?>
