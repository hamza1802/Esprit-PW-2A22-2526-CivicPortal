<?php
/**
 * viewPost.php — View/FrontOffice/viewPost.php
 * View a single forum post with comments.
 * Auth-guarded for write actions only (guests can read).
 * Re-skinned for Parks & Recreation UI.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = !empty($_SESSION['user_id']);

require_once __DIR__ . '/../../Controller/ForumPostController.php';
require_once __DIR__ . '/../../Controller/ForumCommentController.php';
require_once __DIR__ . '/../../Model/ForumComment.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($postId <= 0) {
    header('Location: forum.php');
    exit;
}

$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errors = [];

// Handle POST actions (requires login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_comment') {
        $commentContent = trim($_POST['comment_content'] ?? '');
        if (empty($commentContent) || strlen($commentContent) < 5) {
            $errors[] = 'Comment must be at least 5 characters.';
        } else {
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
        if (empty($commentContent) || strlen($commentContent) < 5) {
            $errors[] = 'Comment must be at least 5 characters.';
        } else {
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
    <title><?= htmlspecialchars($post['title']) ?> | Citizens Forum</title>
    <meta name="description" content="<?= htmlspecialchars(substr($post['content'], 0, 160)) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/forum.css">
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="nav-brand">
            <i class="bi bi-building"></i> CivicPortal
        </div>
        <ul class="nav-links">
            <li><a href="index.php">home</a></li>
            <li><a href="forum.php" style="text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 4px;">forum</a></li>
        </ul>
        <div class="user-controls" style="display:flex; align-items:center; gap:1rem;">
            <?php if ($isLoggedIn): ?>
                <div class="user-role-badge"><?= htmlspecialchars($_SESSION['user_role'] ?? 'citizen') ?></div>
                <span style="font-weight:700; color: var(--primary-navy);"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <section class="page-container">
            <div style="margin-bottom: 2rem;">
                <a href="forum.php" class="forum-back-link"><i class="bi bi-arrow-left"></i> Back to Forum</a>
            </div>

            <?php if ($successMsg): ?>
                <div class="forum-alert forum-alert-success" style="margin-bottom: 2rem;">
                    <i class="bi bi-check-circle"></i> <?= $successMsg ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="forum-alert forum-alert-danger" style="margin-bottom: 2rem;">
                    <?php foreach ($errors as $err): ?>
                        <div><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Single Post -->
            <div class="forum-single-post">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <span class="status-badge"><?= htmlspecialchars($post['category']) ?></span>
                    <span class="status-badge status-<?= $post['status'] === 'open' ? 'pending' : ($post['status'] === 'pinned' ? 'validated' : 'rejected') ?>"><?= strtoupper($post['status']) ?></span>
                </div>
                <h2 style="font-size: clamp(1.5rem, 3vw, 2.5rem); border: none; padding: 0; margin-bottom: 1rem;"><?= htmlspecialchars($post['title']) ?></h2>
                <div class="post-meta" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: var(--border-light);">
                    <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($post['author_name']) ?></span>
                    <span><i class="bi bi-calendar3"></i> <?= date('M d, Y \a\t H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <div style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem; color: var(--text-dark);">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>

                <?php if ($isLoggedIn && $post['user_id'] == $_SESSION['user_id']): ?>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: var(--border-light);">
                        <a href="editPost.php?id=<?= $post['post_id'] ?>" class="btn btn-small"><i class="bi bi-pencil-square"></i> Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                            <input type="hidden" name="action" value="delete_post">
                            <button type="submit" class="btn btn-small btn-danger"><i class="bi bi-trash3"></i> Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <div style="margin-top: 3rem;">
                <h2 style="font-size: clamp(1.5rem, 3vw, 2rem);">Comments <span style="font-weight:400; font-size:0.7em;">(<?= count($comments) ?>)</span></h2>

                <?php if (empty($comments)): ?>
                    <div class="forum-empty-state" style="border-style: solid;">
                        <i class="bi bi-chat-square" style="font-size: 2rem; display: block; margin-bottom: 1rem; opacity: 0.4;"></i>
                        <h3>No Comments Yet</h3>
                        <p>Be the first to share your thoughts.</p>
                    </div>
                <?php else: ?>
                    <div class="forum-comments-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="forum-comment-item" id="comment-<?= $comment['comment_id'] ?>">
                                <?php if ($isLoggedIn && $editCommentId === $comment['comment_id'] && $comment['user_id'] == $_SESSION['user_id']): ?>
                                    <!-- Edit comment form -->
                                    <form method="POST">
                                        <input type="hidden" name="action" value="edit_comment">
                                        <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                        <div class="form-group" style="margin-bottom: 0.5rem;">
                                            <textarea name="comment_content" required minlength="5" rows="3"><?= htmlspecialchars($comment['content']) ?></textarea>
                                        </div>
                                        <div style="display:flex; gap:0.5rem;">
                                            <button type="submit" class="btn btn-small btn-primary">Save</button>
                                            <a href="viewPost.php?id=<?= $postId ?>" class="btn btn-small">Cancel</a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; flex-wrap: wrap; gap: 0.5rem;">
                                        <span style="font-weight: 800; font-size: 0.95rem; color: var(--primary-navy);"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($comment['author_name']) ?></span>
                                        <span style="font-size: 0.8rem; color: var(--primary-navy); opacity: 0.5; font-weight: 600;"><?= date('M d, Y \a\t H:i', strtotime($comment['created_at'])) ?></span>
                                    </div>
                                    <div style="font-size: 1rem; line-height: 1.6; color: var(--text-dark);">
                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                    </div>
                                    <?php if ($isLoggedIn && $comment['user_id'] == $_SESSION['user_id']): ?>
                                        <div style="display: flex; gap: 0.5rem; margin-top: 0.8rem;">
                                            <a href="viewPost.php?id=<?= $postId ?>&edit_comment=<?= $comment['comment_id'] ?>#comment-<?= $comment['comment_id'] ?>" class="btn btn-small"><i class="bi bi-pencil-square"></i> Edit</a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                                <button type="submit" class="btn btn-small btn-danger"><i class="bi bi-trash3"></i> Delete</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <?php if ($post['status'] !== 'closed' && $isLoggedIn): ?>
                    <div class="forum-comment-form">
                        <h3 style="font-size: 1.2rem; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">Leave a Comment</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_comment">
                            <div class="form-group">
                                <textarea name="comment_content" placeholder="Share your thoughts..." required minlength="5" rows="4"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                <?php elseif ($post['status'] === 'closed'): ?>
                    <div class="forum-alert" style="margin-top:2rem; text-align:center;">
                        <i class="bi bi-lock"></i> This discussion has been closed. No new comments can be added.
                    </div>
                <?php elseif (!$isLoggedIn): ?>
                    <div class="forum-alert" style="margin-top:2rem; text-align:center;">
                        <a href="login.php" style="font-weight: 800; color: var(--primary-navy);">Log in</a> to leave a comment.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="../assets/js/glass-animations.js"></script>
</body>
</html>
