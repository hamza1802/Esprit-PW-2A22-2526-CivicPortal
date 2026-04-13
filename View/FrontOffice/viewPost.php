<?php
/**
 * viewPost.php — View/FrontOffice/viewPost.php
 * View a single forum post and all its comments.
 * Allows citizens to add/edit/delete own comments.
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 1;
    $_SESSION['user_name'] = 'John Citizen';
    $_SESSION['user_role'] = 'citizen';
}

require_once __DIR__ . '/../../Controller/ForumPostController.php';
require_once __DIR__ . '/../../Controller/ForumCommentController.php';

// Get post ID
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($postId <= 0) {
    header('Location: forum.php');
    exit;
}

$errors    = [];
$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// Handle comment actions via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_comment') {
        $commentContent = trim($_POST['comment_content'] ?? '');
        
        // Validation
        if (empty($commentContent)) {
            $errors[] = 'Comment content is required.';
        } elseif (strlen($commentContent) < 5) {
            $errors[] = 'Comment must be at least 5 characters.';
        }

        if (empty($errors)) {
            $commentContent = htmlspecialchars($commentContent, ENT_QUOTES, 'UTF-8');
            $comment = new ForumComment(null, $postId, $_SESSION['user_id'], $commentContent);
            ForumCommentController::addComment($comment);
            header("Location: viewPost.php?id=$postId&success=Comment+added+successfully");
            exit;
        }
    }

    if ($action === 'edit_comment') {
        $commentId      = (int)($_POST['comment_id'] ?? 0);
        $commentContent = trim($_POST['comment_content'] ?? '');
        
        if (empty($commentContent)) {
            $errors[] = 'Comment content is required.';
        } elseif (strlen($commentContent) < 5) {
            $errors[] = 'Comment must be at least 5 characters.';
        }

        if (empty($errors)) {
            $commentContent = htmlspecialchars($commentContent, ENT_QUOTES, 'UTF-8');
            ForumCommentController::updateComment($commentId, $_SESSION['user_id'], $commentContent);
            header("Location: viewPost.php?id=$postId&success=Comment+updated+successfully");
            exit;
        }
    }

    if ($action === 'delete_comment') {
        $commentId = (int)($_POST['comment_id'] ?? 0);
        ForumCommentController::deleteComment($commentId, $_SESSION['user_id'], false);
        header("Location: viewPost.php?id=$postId&success=Comment+deleted+successfully");
        exit;
    }

    if ($action === 'delete_post') {
        $success = ForumPostController::deletePost($postId, $_SESSION['user_id'], false);
        if ($success) {
            header('Location: forum.php?success=Post+deleted+successfully');
            exit;
        } else {
            $errors[] = 'Could not delete this post. You may not be the owner.';
        }
    }
}

// Fetch post
$post = ForumPostController::getPostById($postId);
if (!$post) {
    header('Location: forum.php');
    exit;
}

// Fetch comments
$comments = ForumCommentController::getCommentsByPost($postId);
$editCommentId = isset($_GET['edit_comment']) ? (int)$_GET['edit_comment'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | Citizens Forum</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($post['content'], 0, 160)); ?>">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/forum.css">
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="nav-brand">CivicPortal</div>
        <ul class="nav-links">
            <li><a href="index.php">home</a></li>
            <li><a href="forum.php">forum</a></li>
        </ul>
        <div class="user-controls">
            <span class="user-role-badge"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
            <span style="font-weight:700;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
    </nav>

    <main class="forum-container">
        <a href="forum.php" class="back-link">Back to Forum</a>

        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo $successMsg; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div><?php echo htmlspecialchars($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Single Post -->
        <div class="single-post">
            <div class="post-badges" style="margin-bottom: 1rem;">
                <span class="forum-badge badge-<?php echo strtolower($post['category']); ?>">
                    <?php echo htmlspecialchars($post['category']); ?>
                </span>
                <span class="forum-badge badge-<?php echo $post['status']; ?>">
                    <?php echo strtoupper($post['status']); ?>
                </span>
            </div>
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span>By <?php echo htmlspecialchars($post['author_name']); ?></span>
                <span><?php echo date('M d, Y \a\t H:i', strtotime($post['created_at'])); ?></span>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                <div class="post-action-bar">
                    <a href="editPost.php?id=<?php echo $post['post_id']; ?>" class="btn btn-small">Edit</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="action" value="delete_post">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h2>Comments (<?php echo count($comments); ?>)</h2>

            <?php if (empty($comments)): ?>
                <div class="empty-state" style="border-style:solid;">
                    <h3>No Comments Yet</h3>
                    <p>Be the first to share your thoughts.</p>
                </div>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?php echo $comment['comment_id']; ?>">
                            <?php if ($editCommentId === $comment['comment_id'] && $comment['user_id'] == $_SESSION['user_id']): ?>
                                <!-- Edit comment form -->
                                <form method="POST">
                                    <input type="hidden" name="action" value="edit_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                    <textarea name="comment_content" required minlength="5"><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                    <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                                        <button type="submit" class="btn btn-small btn-primary">Save</button>
                                        <a href="viewPost.php?id=<?php echo $postId; ?>" class="btn btn-small">Cancel</a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></span>
                                    <span class="comment-date"><?php echo date('M d, Y \a\t H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                </div>
                                <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                                    <div class="comment-actions">
                                        <a href="viewPost.php?id=<?php echo $postId; ?>&edit_comment=<?php echo $comment['comment_id']; ?>#comment-<?php echo $comment['comment_id']; ?>" class="btn btn-small">Edit</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Add Comment Form -->
            <?php if ($post['status'] !== 'closed'): ?>
                <div class="comment-form">
                    <h3>Leave a Comment</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_comment">
                        <textarea name="comment_content" placeholder="Share your thoughts..." required minlength="5"></textarea>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert" style="margin-top:2rem; text-align:center;">
                    This discussion has been closed. No new comments can be added.
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
