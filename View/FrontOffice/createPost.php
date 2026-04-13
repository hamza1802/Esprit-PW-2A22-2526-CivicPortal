<?php
/**
 * createPost.php — View/FrontOffice/createPost.php
 * Create a new forum post with full input validation.
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 1;
    $_SESSION['user_name'] = 'John Citizen';
    $_SESSION['user_role'] = 'citizen';
}

require_once __DIR__ . '/../../Controller/ForumPostController.php';
require_once __DIR__ . '/../../Model/ForumPost.php';

$errors       = [];
$title        = '';
$content      = '';
$category     = '';
$allowedCats  = ['Infrastructure', 'Health', 'Education'];

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
        // Sanitize
        $title    = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $content  = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // Create object from Model (Blueprint pattern)
        $post = new ForumPost(null, $_SESSION['user_id'], $title, $content, $category);

        // Pass to Controller
        $newId = ForumPostController::createPost($post);
        header("Location: viewPost.php?id=$newId&success=Post+created+successfully");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post | Citizens Forum</title>
    <meta name="description" content="Start a new discussion on the Citizens Forum.">
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

        <div class="forum-form-container">
            <div class="forum-header">
                <h1>New Post</h1>
                <p>Share a topic with the community.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-card">
                <div class="form-group">
                    <label for="post-title">Title</label>
                    <input type="text" id="post-title" name="title" maxlength="255" required
                           value="<?php echo htmlspecialchars($title); ?>"
                           placeholder="Enter a descriptive title...">
                </div>

                <div class="form-group">
                    <label for="post-category">Category</label>
                    <select id="post-category" name="category" required>
                        <option value="">Select a category</option>
                        <?php foreach ($allowedCats as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="post-content">Content</label>
                    <textarea id="post-content" name="content" required minlength="10" rows="8"
                              placeholder="Describe your topic in detail (minimum 10 characters)..."><?php echo htmlspecialchars($content); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Publish Post</button>
            </form>
        </div>
    </main>

</body>
</html>
