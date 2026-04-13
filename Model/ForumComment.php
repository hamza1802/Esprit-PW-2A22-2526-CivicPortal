<?php
/**
 * ForumComment.php — Model/ForumComment.php
 * Blueprint entity for a Forum Comment.
 */

class ForumComment {
    private ?int    $comment_id;
    private int     $post_id;
    private int     $user_id;
    private string  $content;
    private ?string $created_at;

    public function __construct(
        ?int    $comment_id = null,
        int     $post_id    = 0,
        int     $user_id    = 0,
        string  $content    = '',
        ?string $created_at = null
    ) {
        $this->comment_id = $comment_id;
        $this->post_id    = $post_id;
        $this->user_id    = $user_id;
        $this->content    = $content;
        $this->created_at = $created_at;
    }

    // --- Getters ---
    public function getCommentId(): ?int   { return $this->comment_id; }
    public function getPostId(): int       { return $this->post_id; }
    public function getUserId(): int       { return $this->user_id; }
    public function getContent(): string   { return $this->content; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    // --- Setters ---
    public function setCommentId(int $id): void      { $this->comment_id = $id; }
    public function setPostId(int $id): void          { $this->post_id = $id; }
    public function setUserId(int $id): void          { $this->user_id = $id; }
    public function setContent(string $content): void { $this->content = $content; }
    public function setCreatedAt(string $dt): void    { $this->created_at = $dt; }

    public function toArray(): array {
        return [
            'comment_id' => $this->comment_id,
            'post_id'    => $this->post_id,
            'user_id'    => $this->user_id,
            'content'    => $this->content,
            'created_at' => $this->created_at
        ];
    }
}
?>
