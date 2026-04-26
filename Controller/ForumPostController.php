<?php
/**
 * ForumPostController.php — Controller/ForumPostController.php
 * Handles all CRUD operations for forum posts.
 * Rewritten for PDO (integration branch Database.php singleton).
 * JOINs on users.id and users.username (not user_id/name).
 */

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/ForumPost.php';

class ForumPostController {

    private static function getDb() {
        return Database::getInstance()->getConnection();
    }

    /**
     * Get all posts with optional category/status filters.
     *
     * PERFORMANCE — N+1 fix:
     *   The previous implementation called getCommentCount() inside the render loop, firing
     *   one extra SELECT COUNT(*) per post (100 posts = 101 queries).
     *   This query includes a correlated subquery that retrieves the comment count in a single
     *   round-trip. The result column `comment_count` replaces the separate call entirely.
     *
     * SECURITY:
     *   Category and status are validated against strict allowlists before interpolation.
     *   PDO prepared statements bind all user-controlled values as parameters.
     */
    private static array $allowedCategories = ['Infrastructure', 'Health', 'Education'];
    private static array $allowedStatuses   = ['open', 'closed', 'pinned'];

    public static function getAllPosts(?string $category = null, ?string $status = null): array {
        $db     = self::getDb();
        $params = [];

        $sql = "SELECT fp.*,
                       u.username AS author_name,
                       (SELECT COUNT(*) FROM forum_comments fc WHERE fc.post_id = fp.post_id) AS comment_count
                FROM forum_posts fp
                JOIN users u ON fp.user_id = u.id
                WHERE 1=1";

        if ($category && in_array($category, self::$allowedCategories, true)) {
            $sql     .= " AND fp.category = ?";
            $params[] = $category;
        }
        if ($status && in_array($status, self::$allowedStatuses, true)) {
            $sql     .= " AND fp.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY FIELD(fp.status, 'pinned', 'open', 'closed'), fp.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get a single post by ID.
     */
    public static function getPostById(int $postId): ?array {
        $db = self::getDb();
        $stmt = $db->prepare(
            "SELECT fp.*, u.username AS author_name 
             FROM forum_posts fp 
             JOIN users u ON fp.user_id = u.id 
             WHERE fp.post_id = ?"
        );
        $stmt->execute([$postId]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    /**
     * Create a new post. Returns the new post_id on success.
     */
    public static function createPost(ForumPost $post): int {
        $db = self::getDb();
        $stmt = $db->prepare(
            "INSERT INTO forum_posts (user_id, title, content, category, status) 
             VALUES (?, ?, ?, ?, 'open')"
        );
        $stmt->execute([
            $post->getUserId(),
            $post->getTitle(),
            $post->getContent(),
            $post->getCategory()
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Update an existing post. Only the owner can edit.
     */
    public static function updatePost(int $postId, int $userId, string $title, string $content, string $category): bool {
        $db = self::getDb();
        $stmt = $db->prepare(
            "UPDATE forum_posts 
             SET title = ?, content = ?, category = ? 
             WHERE post_id = ? AND user_id = ?"
        );
        $stmt->execute([$title, $content, $category, $postId, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a post. Only the owner or an admin can delete.
     */
    public static function deletePost(int $postId, int $userId, bool $isAdmin = false): bool {
        $db = self::getDb();
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM forum_posts WHERE post_id = ?");
            $stmt->execute([$postId]);
        } else {
            $stmt = $db->prepare("DELETE FROM forum_posts WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Update a post's status (pin / close / open). Admin only.
     */
    public static function updateStatus(int $postId, string $newStatus): bool {
        if (!in_array($newStatus, ['open', 'closed', 'pinned'])) {
            return false;
        }
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE forum_posts SET status = ? WHERE post_id = ?");
        $stmt->execute([$newStatus, $postId]);
        return $stmt->rowCount() >= 0;
    }

    /**
     * Count comments for a post.
     */
    public static function getCommentCount(int $postId): int {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM forum_comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
    }
}
?>
