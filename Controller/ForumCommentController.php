<?php
/**
 * ForumCommentController.php — Controller/ForumCommentController.php
 * Handles all CRUD operations for forum comments.
 * Rewritten for PDO (integration branch Database.php singleton).
 * JOINs on users.id and users.username (not user_id/name).
 */

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/ForumComment.php';

class ForumCommentController {

    private static function getDb() {
        return Database::getInstance()->getConnection();
    }

    /**
     * Get all comments for a given post.
     */
    public static function getCommentsByPost(int $postId): array {
        $db = self::getDb();
        $stmt = $db->prepare(
            "SELECT fc.*, u.username AS author_name 
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.id 
             WHERE fc.post_id = ? 
             ORDER BY fc.created_at ASC"
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all comments (admin dashboard view).
     */
    public static function getAllComments(): array {
        $db = self::getDb();
        $stmt = $db->prepare(
            "SELECT fc.*, u.username AS author_name, fp.title AS post_title
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.id 
             JOIN forum_posts fp ON fc.post_id = fp.post_id
             ORDER BY fc.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add a comment to a post.
     */
    public static function addComment(ForumComment $comment): int {
        $db = self::getDb();
        $stmt = $db->prepare(
            "INSERT INTO forum_comments (post_id, user_id, content) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $comment->getPostId(),
            $comment->getUserId(),
            $comment->getContent()
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Update a comment. Only the owner can edit.
     */
    public static function updateComment(int $commentId, int $userId, string $content): bool {
        $db = self::getDb();
        $stmt = $db->prepare(
            "UPDATE forum_comments SET content = ? WHERE comment_id = ? AND user_id = ?"
        );
        $stmt->execute([$content, $commentId, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a comment. Owner or admin can delete.
     */
    public static function deleteComment(int $commentId, int $userId, bool $isAdmin = false): bool {
        $db = self::getDb();
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM forum_comments WHERE comment_id = ?");
            $stmt->execute([$commentId]);
        } else {
            $stmt = $db->prepare("DELETE FROM forum_comments WHERE comment_id = ? AND user_id = ?");
            $stmt->execute([$commentId, $userId]);
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Get a single comment by ID.
     */
    public static function getCommentById(int $commentId): ?array {
        $db = self::getDb();
        $stmt = $db->prepare(
            "SELECT fc.*, u.username AS author_name 
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.id 
             WHERE fc.comment_id = ?"
        );
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        return $comment ?: null;
    }
}
?>
