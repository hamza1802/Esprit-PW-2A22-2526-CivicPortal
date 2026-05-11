<?php
/**
 * forum.php — View/FrontOffice/forum.php
 * Citizens Forum - List all posts with filters.
 * Re-skinned for Parks & Recreation UI. Auth-aware.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guest CAN view forum index — no redirect needed
$isLoggedIn = !empty($_SESSION['user_id']);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/forum.css">
</head>
<body>
    <div class="aurora-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Navigation — matches main platform nav -->
    <nav>
        <div class="nav-brand">
            <i class="bi bi-building"></i> CivicPortal
        </div>
        <div class="nav-backdrop"></div>
        <button class="nav-hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="index.php">home</a></li>
            <li><a href="index.php#programs">programs</a></li>
            <li><a href="forum.php" class="active">forum</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="index.php#request-service">requests</a></li>
                <li><a href="index.php#appointments">appointments</a></li>
                <li><a href="index.php#transport">transport</a></li>
                <li><a href="index.php#dashboard">dashboard</a></li>
                <li><a href="index.php#profile">profile</a></li>
            <?php endif; ?>
        </ul>
        <div class="user-controls">
            <?php if ($isLoggedIn): ?>
                <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'agent'])): ?>
                    <a href="../BackOffice/index.php" class="user-role-badge" style="text-decoration:none;">Staff Portal</a>
                <?php else: ?>
                    <div class="user-role-badge">Citizen</div>
                <?php endif; ?>
                <a href="#" onclick="event.preventDefault(); fetch('../../Verification.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'logout'}) }).then(() => window.location.href='login.php')" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <!-- Hero Header -->
        <div class="hero-container">
            <section class="hero-section" style="padding-bottom: 2rem;">
                <h1 style="font-size: clamp(2rem, 6vw, 5rem);">Citizens Forum</h1>
                <p>Discuss community issues, share ideas, and collaborate with fellow citizens.</p>
            </section>
        </div>

        <section class="page-container">

            <?php if ($successMsg): ?>
                <div class="forum-alert forum-alert-success" style="margin-bottom: 2rem;">
                    <i class="bi bi-check-circle"></i> <?= $successMsg ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <form class="forum-filters" method="GET" action="forum.php" id="forum-filters-form">
                <div class="filter-group">
                    <label for="filter-category">Category</label>
                    <select name="category" id="filter-category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="General" <?= $filterCategory === 'General' ? 'selected' : '' ?>>General</option>
                        <option value="Transport" <?= $filterCategory === 'Transport' ? 'selected' : '' ?>>Transport</option>
                        <option value="Events" <?= $filterCategory === 'Events' ? 'selected' : '' ?>>Events</option>
                        <option value="Announcements" <?= $filterCategory === 'Announcements' ? 'selected' : '' ?>>Announcements</option>
                        <option value="Suggestions" <?= $filterCategory === 'Suggestions' ? 'selected' : '' ?>>Suggestions</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-status">Status</label>
                    <select name="status" id="filter-status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="open" <?= $filterStatus === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= $filterStatus === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="pinned" <?= $filterStatus === 'pinned' ? 'selected' : '' ?>>Pinned</option>
                    </select>
                </div>
                <div class="forum-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="createPost.php" class="btn btn-primary">+ New Post</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Post</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Posts List -->
            <?php if (empty($posts)): ?>
                <div class="forum-empty-state">
                    <i class="bi bi-chat-square-text" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.4;"></i>
                    <h3>No Posts Yet</h3>
                    <p>Be the first to start a discussion in the Citizens Forum.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="createPost.php" class="btn btn-primary" style="margin-top: 1rem;">Create a Post</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="posts-list">
                    <?php foreach ($posts as $post): ?>
                        <a href="viewPost.php?id=<?= $post['post_id'] ?>" class="post-card">
                            <div class="post-card-header">
                                <span class="post-title"><?= htmlspecialchars($post['title']) ?></span>
                                <div class="post-badges">
                                    <span class="status-badge"><?= htmlspecialchars($post['category']) ?></span>
                                    <span class="status-badge status-<?= $post['status'] === 'open' ? 'pending' : ($post['status'] === 'pinned' ? 'validated' : 'rejected') ?>"><?= strtoupper($post['status']) ?></span>
                                    <?php if (!empty($post['ai_flag']) && $post['ai_flag'] !== 'clean'): ?>
                                        <span class="ai-badge ai-badge-<?= $post['ai_flag'] ?>" title="<?= htmlspecialchars($post['ai_reason'] ?? '') ?>">
                                            <i class="bi bi-robot"></i> <?= strtoupper($post['ai_flag']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($post['ai_urgency']) && $post['ai_urgency'] !== 'low'): ?>
                                        <span class="ai-badge ai-urgency-<?= $post['ai_urgency'] ?>">
                                            <i class="bi bi-exclamation-diamond"></i> <?= strtoupper($post['ai_urgency']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="post-excerpt"><?= htmlspecialchars(substr($post['content'], 0, 200)) ?></p>
                            <div class="post-meta">
                                <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($post['author_name']) ?></span>
                                <span><i class="bi bi-calendar3"></i> <?= date('M d, Y \a\t H:i', strtotime($post['created_at'])) ?></span>
                                <span><i class="bi bi-chat-dots"></i> <?= ForumPostController::getCommentCount($post['post_id']) ?> comments</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
    // Hamburger nav toggle
    (function() {
        const nav = document.querySelector('nav');
        const hamburger = nav.querySelector('.nav-hamburger');
        const backdrop = nav.querySelector('.nav-backdrop');
        if (hamburger) {
            const toggle = () => nav.classList.toggle('nav-open');
            hamburger.addEventListener('click', toggle);
            if (backdrop) backdrop.addEventListener('click', toggle);
            nav.querySelectorAll('.nav-links a').forEach(a => {
                a.addEventListener('click', () => nav.classList.remove('nav-open'));
            });
        }
    })();
    </script>
    <script src="../assets/js/glass-animations.js"></script>
</body>
</html>
