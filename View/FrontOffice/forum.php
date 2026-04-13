<?php
/**
 * forum.php — View/FrontOffice/forum.php
 * Citizens Forum - List all posts with filters.
 */
session_start();

// Simulate logged-in citizen if not set (demo mode)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 1;
    $_SESSION['user_name'] = 'John Citizen';
    $_SESSION['user_role'] = 'citizen';
}

require_once __DIR__ . '/../../Controller/ForumPostController.php';

// Read filters from GET parameters
$filterCategory = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$filterStatus   = isset($_GET['status']) && $_GET['status'] !== ''   ? $_GET['status']   : null;

// Fetch posts
$posts = ForumPostController::getAllPosts($filterCategory, $filterStatus);

// Success message from redirects
$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizens Forum | CivicPortal</title>
    <meta name="description" content="Discuss community issues on CivicPortal's Citizens Forum.">
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
        <!-- Header -->
        <div class="forum-header">
            <h1>Citizens Forum</h1>
            <p>Discuss community issues, share ideas, and collaborate with fellow citizens.</p>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo $successMsg; ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <form class="forum-filters" method="GET" action="forum.php" id="forum-filters-form">
            <div class="filter-group">
                <label for="filter-category">Category</label>
                <select name="category" id="filter-category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="Infrastructure" <?php echo $filterCategory === 'Infrastructure' ? 'selected' : ''; ?>>Infrastructure</option>
                    <option value="Health" <?php echo $filterCategory === 'Health' ? 'selected' : ''; ?>>Health</option>
                    <option value="Education" <?php echo $filterCategory === 'Education' ? 'selected' : ''; ?>>Education</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="open" <?php echo $filterStatus === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="closed" <?php echo $filterStatus === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    <option value="pinned" <?php echo $filterStatus === 'pinned' ? 'selected' : ''; ?>>Pinned</option>
                </select>
            </div>
            <div class="forum-actions">
                <a href="createPost.php" class="btn btn-primary">+ New Post</a>
            </div>
        </form>

        <!-- Posts List -->
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h3>No Posts Yet</h3>
                <p>Be the first to start a discussion in the Citizens Forum.</p>
                <a href="createPost.php" class="btn btn-primary">Create a Post</a>
            </div>
        <?php else: ?>
            <div class="posts-list">
                <?php foreach ($posts as $post): ?>
                    <a href="viewPost.php?id=<?php echo $post['post_id']; ?>" class="post-card">
                        <div class="post-card-header">
                            <span class="post-title"><?php echo htmlspecialchars($post['title']); ?></span>
                            <div class="post-badges">
                                <span class="forum-badge badge-<?php echo strtolower($post['category']); ?>">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                                <span class="forum-badge badge-<?php echo $post['status']; ?>">
                                    <?php echo strtoupper($post['status']); ?>
                                </span>
                            </div>
                        </div>
                        <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 200)); ?></p>
                        <div class="post-meta">
                            <span>By <?php echo htmlspecialchars($post['author_name']); ?></span>
                            <span><?php echo date('M d, Y \a\t H:i', strtotime($post['created_at'])); ?></span>
                            <span><?php echo ForumPostController::getCommentCount($post['post_id']); ?> comments</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
