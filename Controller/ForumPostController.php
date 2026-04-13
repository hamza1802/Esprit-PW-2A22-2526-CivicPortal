<?php
/**
 * ForumPostController.php — Controller/ForumPostController.php
 * Handles all CRUD operations for forum posts using mysqli prepared statements.
 */

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/ForumPost.php';

class ForumPostController {

    /**
     * Get all posts, with optional category and status filters.
     */
    public static function getAllPosts(?string $category = null, ?string $status = null): array {
        $conn = Database::getConnection();

        $sql = "SELECT fp.*, u.name AS author_name 
                FROM forum_posts fp 
                JOIN users u ON fp.user_id = u.user_id 
                WHERE 1=1";
        $params = [];
        $types  = '';

        if ($category && in_array($category, ['Infrastructure', 'Health', 'Education'])) {
            $sql .= " AND fp.category = ?";
            $params[] = $category;
            $types .= 's';
        }
        if ($status && in_array($status, ['open', 'closed', 'pinned'])) {
            $sql .= " AND fp.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        $sql .= " ORDER BY FIELD(fp.status, 'pinned', 'open', 'closed'), fp.created_at DESC";

        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        $stmt->close();
        return $posts;
    }

    /**
     * Get a single post by ID.
     */
    public static function getPostById(int $postId): ?array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "SELECT fp.*, u.name AS author_name 
             FROM forum_posts fp 
             JOIN users u ON fp.user_id = u.user_id 
             WHERE fp.post_id = ?"
        );
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
        return $post ?: null;
    }

    /**
     * Create a new post. Returns the new post_id on success.
     */
    public static function createPost(ForumPost $post): int {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO forum_posts (user_id, title, content, category, status) 
             VALUES (?, ?, ?, ?, 'open')"
        );
        $userId   = $post->getUserId();
        $title    = $post->getTitle();
        $content  = $post->getContent();
        $category = $post->getCategory();
        $stmt->bind_param('isss', $userId, $title, $content, $category);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Update an existing post. Only the owner can edit.
     */
    public static function updatePost(int $postId, int $userId, string $title, string $content, string $category): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "UPDATE forum_posts 
             SET title = ?, content = ?, category = ? 
             WHERE post_id = ? AND user_id = ?"
        );
        $stmt->bind_param('sssii', $title, $content, $category, $postId, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Delete a post. Only the owner or an admin can delete.
     */
    public static function deletePost(int $postId, int $userId, bool $isAdmin = false): bool {
        $conn = Database::getConnection();
        if ($isAdmin) {
            $stmt = $conn->prepare("DELETE FROM forum_posts WHERE post_id = ?");
            $stmt->bind_param('i', $postId);
        } else {
            $stmt = $conn->prepare("DELETE FROM forum_posts WHERE post_id = ? AND user_id = ?");
            $stmt->bind_param('ii', $postId, $userId);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Update a post's status (pin / close / open). Admin only.
     */
    public static function updateStatus(int $postId, string $newStatus): bool {
        if (!in_array($newStatus, ['open', 'closed', 'pinned'])) {
            return false;
        }
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE forum_posts SET status = ? WHERE post_id = ?");
        $stmt->bind_param('si', $newStatus, $postId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected >= 0; // 0 rows if status unchanged, still valid
    }

    /**
     * Count comments for a post.
     */
    public static function getCommentCount(int $postId): int {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM forum_comments WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['cnt'];
    }
}
?>
