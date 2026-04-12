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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav>
    <div class="nav-brand">CivicPortal</div>
    <ul class="nav-links">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <li><a href="index.php?page=front_home">Home</a></li>
            <li><a href="index.php?page=front_profile">Profile</a></li>
            <li><a href="index.php?page=back_users_list">Management</a></li>
            <li><a href="index.php?action=logout">Logout</a></li>
        <?php else: ?>
            <li><a href="index.php?page=front_login">Login</a></li>
            <li><a href="index.php?page=front_register">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
<main class="page-container">
