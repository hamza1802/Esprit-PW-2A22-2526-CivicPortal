<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = $_GET['page'] ?? 'front_login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - User Management</title>
    <!-- Try both common local paths for style.css to be safe -->
    <link rel="stylesheet" href="View/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav style="display: flex; align-items: center; justify-content: space-between; padding: 1.5rem 2rem; background-color: #3A86FF; border-bottom: 2px solid #1D2A44; position: sticky; top: 0; z-index: 1000;">
    <a href="index.php?page=back_users_list" class="nav-brand" style="text-decoration:none; text-transform:uppercase; color: #1D2A44; font-weight: 900; font-size: 1.5rem; letter-spacing: -1px;">CIVICPORTAL STAFF</a>
    <ul class="nav-links" style="display:flex; list-style:none; margin:0; padding:0; gap:2rem;">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <li><a href="index.php?page=front_home" style="text-transform:lowercase; color:#1D2A44; text-decoration:none; font-weight:700; font-size: 1.1rem;">home</a></li>
            <li><a href="index.php?page=front_profile" style="text-transform:lowercase; color:#1D2A44; text-decoration:none; font-weight:700; font-size: 1.1rem;">profile</a></li>
        <?php else: ?>
            <li><a href="index.php?page=front_login" style="text-transform:lowercase; color:#1D2A44; text-decoration:none; font-weight:700; font-size: 1.1rem;">login</a></li>
            <li><a href="index.php?page=front_register" style="text-transform:lowercase; color:#1D2A44; text-decoration:none; font-weight:700; font-size: 1.1rem;">register</a></li>
        <?php endif; ?>
    </ul>
    <div class="nav-actions" style="display:flex; gap:1rem; align-items:center;">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="index.php?page=front_home" class="nav-btn-outlined" style="border:2px solid #1D2A44; color:#1D2A44; padding:0.5rem 1.2rem; text-decoration:none; border-radius:4px; font-weight:800; text-transform:uppercase; font-size: 0.8rem; letter-spacing: 1px; background: transparent;">FRONTOFFICE</a>
            <span class="user-role-badge" style="background:#1D2A44; color:#F0EADC; padding:0.5rem 1.2rem; border-radius:4px; font-weight:800; text-transform:uppercase; font-size: 0.8rem; letter-spacing: 1px; cursor: default;"><?= htmlspecialchars($_SESSION['user_role'] ?? 'USER') ?></span>
        <?php endif; ?>
    </div>
</nav>
<main class="page-container" style="padding: 4rem 5%; max-width: 1600px; margin: 0 auto;">
