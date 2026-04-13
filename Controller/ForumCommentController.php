<?php
/**
 * ForumCommentController.php — Controller/ForumCommentController.php
 * Handles all CRUD operations for forum comments using mysqli prepared statements.
 */

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/ForumComment.php';

class ForumCommentController {

    /**
     * Get all comments for a given post.
     */
    public static function getCommentsByPost(int $postId): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "SELECT fc.*, u.name AS author_name 
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.user_id 
             WHERE fc.post_id = ? 
             ORDER BY fc.created_at ASC"
        );
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();

        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        $stmt->close();
        return $comments;
    }

    /**
     * Get all comments (admin dashboard view).
     */
    public static function getAllComments(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "SELECT fc.*, u.name AS author_name, fp.title AS post_title
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.user_id 
             JOIN forum_posts fp ON fc.post_id = fp.post_id
             ORDER BY fc.created_at DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        $stmt->close();
        return $comments;
    }

    /**
     * Add a comment to a post.
     */
    public static function addComment(ForumComment $comment): int {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO forum_comments (post_id, user_id, content) VALUES (?, ?, ?)"
        );
        $postId  = $comment->getPostId();
        $userId  = $comment->getUserId();
        $content = $comment->getContent();
        $stmt->bind_param('iis', $postId, $userId, $content);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Update a comment. Only the owner can edit.
     */
    public static function updateComment(int $commentId, int $userId, string $content): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "UPDATE forum_comments SET content = ? WHERE comment_id = ? AND user_id = ?"
        );
        $stmt->bind_param('sii', $content, $commentId, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Delete a comment. Owner or admin can delete.
     */
    public static function deleteComment(int $commentId, int $userId, bool $isAdmin = false): bool {
        $conn = Database::getConnection();
        if ($isAdmin) {
            $stmt = $conn->prepare("DELETE FROM forum_comments WHERE comment_id = ?");
            $stmt->bind_param('i', $commentId);
        } else {
            $stmt = $conn->prepare("DELETE FROM forum_comments WHERE comment_id = ? AND user_id = ?");
            $stmt->bind_param('ii', $commentId, $userId);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Get a single comment by ID.
     */
    public static function getCommentById(int $commentId): ?array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "SELECT fc.*, u.name AS author_name 
             FROM forum_comments fc 
             JOIN users u ON fc.user_id = u.user_id 
             WHERE fc.comment_id = ?"
        );
        $stmt->bind_param('i', $commentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = $result->fetch_assoc();
        $stmt->close();
        return $comment ?: null;
    }
}
?>
