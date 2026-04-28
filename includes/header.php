<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$currentPage = $_GET['page'] ?? 'front_login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - User Management</title>
    <!-- Try both common local paths for style.css to be safe -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="View/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="View/assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">
<nav>
    <a href="index.php?page=back_dashboard" class="nav-brand">CIVICPORTAL STAFF</a>
    <ul class="nav-links">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <li><a href="index.php?page=front_home">home</a></li>
            <li><a href="index.php?page=front_profile">profile</a></li>
        <?php else: ?>
            <li><a href="index.php?page=front_login">login</a></li>
            <li><a href="index.php?page=front_register">register</a></li>
        <?php endif; ?>
    </ul>
    <div class="user-controls">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="index.php?page=front_home" class="btn btn-small">FRONTOFFICE</a>
            <span class="user-role-badge"><?= htmlspecialchars($_SESSION['user_role'] ?? 'USER') ?></span>
        <?php endif; ?>
    </div>
</nav>
<main class="page-container" style="flex: 1; width: 100%;">
