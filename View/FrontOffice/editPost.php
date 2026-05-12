<?php
/**
 * editPost.php — View/FrontOffice/editPost.php
 * Edit an existing forum post (owner only).
 * Auth-guarded. Re-skinned for Parks & Recreation UI.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth guard
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../Controller/ForumPostController.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($postId <= 0) {
    header('Location: forum.php');
    exit;
}

// Fetch existing post
$post = ForumPostController::getPostById($postId);
if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    header('Location: forum.php');
    exit;
}

$errors       = [];
$title        = $post['title'];
$content      = $post['content'];
$category     = $post['category'];
$allowedCats  = ['General', 'Transport', 'Events', 'Announcements', 'Suggestions'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must not exceed 255 characters.';
    }

    if (empty($content)) {
        $errors[] = 'Content is required.';
    } elseif (strlen($content) < 10) {
        $errors[] = 'Content must be at least 10 characters.';
    }

    if (empty($category)) {
        $errors[] = 'Category is required.';
    } elseif (!in_array($category, $allowedCats)) {
        $errors[] = 'Invalid category selected.';
    }

    if (empty($errors)) {
        $title   = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $success = ForumPostController::updatePost($postId, $_SESSION['user_id'], $title, $content, $category);
        if ($success) {
            header("Location: viewPost.php?id=$postId&success=Post+updated+successfully");
            exit;
        } else {
            $errors[] = 'Failed to update the post.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | Citizens Forum</title>
    <meta name="description" content="Edit your forum post.">
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

    <!-- Navigation -->
    <nav>
        <a href="index.php" class="nav-brand" style="text-decoration:none;">
            <img src="../assets/images/logo.png" alt="CivicPortal" class="nav-logo">
        </a>
        <div class="nav-backdrop"></div>
        <button class="nav-hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="index.php#programs">programs</a></li>
            <li><a href="forum.php" class="active">forum</a></li>
            <li><a href="index.php#request-service">requests</a></li>
            <li><a href="index.php#appointments">appointments</a></li>
            <li><a href="index.php#transport">transport</a></li>
            <li><a href="index.php#dashboard">dashboard</a></li>
            <li><a href="index.php#profile">profile</a></li>
        </ul>
        <div class="user-controls">
            <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'agent'])): ?>
                <a href="../BackOffice/index.php" class="user-role-badge" style="text-decoration:none;">Staff Portal</a>
            <?php else: ?>
                <div class="user-role-badge">Citizen</div>
            <?php endif; ?>
            <a href="#" onclick="event.preventDefault(); fetch('../../Verification.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'logout'}) }).then(() => window.location.href='login.php')" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </nav>

    <main>
        <section class="page-container">
            <div style="margin-bottom: 2rem;">
                <a href="viewPost.php?id=<?= $postId ?>" class="forum-back-link"><i class="bi bi-arrow-left"></i> Back to Post</a>
            </div>

            <div style="max-width: 800px; margin: 0 auto;">
                <h2>Edit Post</h2>
                <p style="margin-bottom: 2rem;">Update your discussion topic.</p>

                <?php if (!empty($errors)): ?>
                    <div class="forum-alert forum-alert-danger" style="margin-bottom: 2rem;">
                        <?php foreach ($errors as $err): ?>
                            <div><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($err) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-card">
                    <div class="form-group">
                        <label for="post-title">Title</label>
                        <input type="text" id="post-title" name="title" maxlength="255" required
                               value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="form-group">
                        <label for="post-category">Category</label>
                        <select id="post-category" name="category" required>
                            <option value="">Select a category</option>
                            <?php foreach ($allowedCats as $cat): ?>
                                <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                    <?= $cat ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="post-content">Content</label>
                        <textarea id="post-content" name="content" required minlength="10" rows="8"><?= htmlspecialchars($content) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Post</button>
                </form>
            </div>
        </section>
    </main>

    <script>
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
