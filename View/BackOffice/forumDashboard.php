<?php
/**
 * forumDashboard.php — View/BackOffice/forumDashboard.php
 * Admin dashboard for managing all forum posts and comments.
 */
session_start();

// Simulate admin session (demo mode)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 3;
    $_SESSION['user_name'] = 'Admin User';
    $_SESSION['user_role'] = 'admin';
}

// Role check — only admins
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ../FrontOffice/forum.php');
    exit;
}

require_once __DIR__ . '/../../Controller/ForumPostController.php';
require_once __DIR__ . '/../../Controller/ForumCommentController.php';

$errors     = [];
$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// Handle admin POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Pin a post
    if ($action === 'pin_post') {
        $postId = (int)($_POST['post_id'] ?? 0);
        ForumPostController::updateStatus($postId, 'pinned');
        header("Location: forumDashboard.php?success=Post+pinned+successfully");
        exit;
    }

    // Close a post
    if ($action === 'close_post') {
        $postId = (int)($_POST['post_id'] ?? 0);
        ForumPostController::updateStatus($postId, 'closed');
        header("Location: forumDashboard.php?success=Post+closed+successfully");
        exit;
    }

    // Reopen a post
    if ($action === 'open_post') {
        $postId = (int)($_POST['post_id'] ?? 0);
        ForumPostController::updateStatus($postId, 'open');
        header("Location: forumDashboard.php?success=Post+reopened+successfully");
        exit;
    }

    // Delete a post (cascades to comments)
    if ($action === 'delete_post') {
        $postId = (int)($_POST['post_id'] ?? 0);
        ForumPostController::deletePost($postId, $_SESSION['user_id'], true);
        header("Location: forumDashboard.php?success=Post+deleted+successfully");
        exit;
    }

    // Remove a comment
    if ($action === 'delete_comment') {
        $commentId = (int)($_POST['comment_id'] ?? 0);
        ForumCommentController::deleteComment($commentId, $_SESSION['user_id'], true);
        header("Location: forumDashboard.php?success=Comment+removed+successfully");
        exit;
    }
}

// Fetch all data
$posts    = ForumPostController::getAllPosts();
$comments = ForumCommentController::getAllComments();

// Stats
$totalPosts    = count($posts);
$totalComments = count($comments);
$openPosts     = count(array_filter($posts, fn($p) => $p['status'] === 'open'));
$pinnedPosts   = count(array_filter($posts, fn($p) => $p['status'] === 'pinned'));
$closedPosts   = count(array_filter($posts, fn($p) => $p['status'] === 'closed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Dashboard | CivicPortal Admin</title>
    <meta name="description" content="Admin dashboard for managing the Citizens Forum.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/forum.css">
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="nav-brand" style="color: var(--danger);">CivicPortal Admin</div>
        <ul class="nav-links">
            <li><a href="index.php">dashboard</a></li>
            <li><a href="forumDashboard.php">forum mgmt</a></li>
            <li><a href="../FrontOffice/forum.php">view forum</a></li>
        </ul>
        <div class="user-controls">
            <span class="user-role-badge" style="background:var(--danger); border-color:var(--danger);">Admin</span>
            <span style="font-weight:700;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
    </nav>

    <main class="forum-container">
        <div class="forum-header">
            <h1>Forum Management</h1>
            <p>Manage all forum posts and comments across the platform.</p>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo $successMsg; ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalPosts; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $openPosts; ?></div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pinnedPosts; ?></div>
                <div class="stat-label">Pinned</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $closedPosts; ?></div>
                <div class="stat-label">Closed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalComments; ?></div>
                <div class="stat-label">Comments</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="admin-tabs">
            <button class="admin-tab active" onclick="switchTab('posts')">Posts</button>
            <button class="admin-tab" onclick="switchTab('comments')">Comments</button>
        </div>

        <!-- Posts Tab -->
        <div class="tab-content active" id="tab-posts">
            <?php if (empty($posts)): ?>
                <div style="padding:3rem; text-align:center;">
                    <p style="font-weight:700;">No posts found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['post_id']; ?></td>
                                    <td>
                                        <a href="../FrontOffice/viewPost.php?id=<?php echo $post['post_id']; ?>" style="color:var(--primary-navy); font-weight:700; text-decoration:none;">
                                            <?php echo htmlspecialchars(substr($post['title'], 0, 50)); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td>
                                        <span class="forum-badge badge-<?php echo strtolower($post['category']); ?>">
                                            <?php echo htmlspecialchars($post['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="forum-badge badge-<?php echo $post['status']; ?>">
                                            <?php echo strtoupper($post['status']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.85rem;"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <?php if ($post['status'] !== 'pinned'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="pin_post">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                    <button type="submit" class="btn btn-small">Pin</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($post['status'] !== 'closed'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="close_post">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                    <button type="submit" class="btn btn-small">Close</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($post['status'] !== 'open'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="open_post">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                    <button type="submit" class="btn btn-small btn-success">Reopen</button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this post and all its comments?');">
                                                <input type="hidden" name="action" value="delete_post">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Comments Tab -->
        <div class="tab-content" id="tab-comments">
            <?php if (empty($comments)): ?>
                <div style="padding:3rem; text-align:center;">
                    <p style="font-weight:700;">No comments found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Post</th>
                                <th>Author</th>
                                <th>Content</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['comment_id']; ?></td>
                                    <td>
                                        <a href="../FrontOffice/viewPost.php?id=<?php echo $comment['post_id']; ?>" style="color:var(--primary-navy); font-weight:700; text-decoration:none;">
                                            <?php echo htmlspecialchars(substr($comment['post_title'], 0, 30)); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($comment['author_name']); ?></td>
                                    <td style="font-size:0.9rem;"><?php echo htmlspecialchars(substr($comment['content'], 0, 80)); ?></td>
                                    <td style="font-size:0.85rem;"><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this comment?');">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                            <button type="submit" class="btn btn-small btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.remove('active');
            });
            document.querySelectorAll('.admin-tab').forEach(function(el) {
                el.classList.remove('active');
            });

            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

</body>
</html>
