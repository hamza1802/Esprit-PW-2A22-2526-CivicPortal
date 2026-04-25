<?php
/**
 * partials/forum_nav.php
 * Shared navigation bar for all forum pages.
 *
 * ARCHITECTURE — why a partial instead of copy-paste:
 *  • Single source of truth: changing nav links, branding, or logout logic requires
 *    editing exactly ONE file instead of four.
 *  • DRY principle: the nav was verbatim-duplicated across forum.php, createPost.php,
 *    viewPost.php, and editPost.php — a classic maintenance liability.
 *
 * Requires (callers must define these before including):
 *   bool   $isLoggedIn   — whether a valid session exists
 *   string $activeNav    — href value of the currently active link (e.g. 'forum.php')
 */

// Guard: this file should never be the entry point.
if (!defined('_CIVICPORTAL_BOOTSTRAP_')) {
    http_response_code(403);
    exit;
}
?>
<nav>
    <div class="nav-brand">
        <i class="bi bi-building"></i> CivicPortal
    </div>
    <ul class="nav-links">
        <li><a href="index.php"<?= $activeNav === 'index.php' ? ' aria-current="page"' : '' ?>>home</a></li>
        <li><a href="forum.php"<?= $activeNav === 'forum.php' ? ' style="text-decoration:underline;text-decoration-thickness:2px;text-underline-offset:4px;" aria-current="page"' : '' ?>>forum</a></li>
        <?php if ($isLoggedIn): ?>
            <li><a href="index.php#request-service">requests</a></li>
            <li><a href="index.php#transport">transport</a></li>
        <?php endif; ?>
    </ul>
    <div class="user-controls" style="display:flex;align-items:center;gap:1rem;">
        <?php if ($isLoggedIn): ?>
            <div class="user-role-badge"><?= htmlspecialchars($_SESSION['user_role'] ?? 'citizen') ?></div>
            <span style="font-weight:700;color:var(--primary-navy);">
                <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
            </span>
            <a href="#" onclick="
                event.preventDefault();
                fetch('../../Verification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                }).then(() => window.location.href = 'login.php');"
               style="color:var(--danger);font-weight:600;text-decoration:none;font-size:0.9rem;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary" style="padding:0.5rem 1.5rem;font-size:0.9rem;">Login</a>
        <?php endif; ?>
    </div>
</nav>
