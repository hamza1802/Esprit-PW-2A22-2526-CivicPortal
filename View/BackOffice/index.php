<?php
/**
 * BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal
 */
require_once '../../Model/AppModel.php';
AppModel::init();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Staff Portal</title>
    <meta name="description" content="Administrative and Worker functionalities for CivicPortal.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Quick override for staff portal header styling if needed */
        .nav-brand { color: var(--primary-red); }
    </style>
</head>
<body>

    <!-- Header & Navigation -->
    <nav>
        <!-- Injected via view.js -->
    </nav>

    <!-- Main Content Area -->
    <main id="app">
        <!-- Dynamic content injected here -->
        <div class="uv-loader-wrapper">
            <div class="uv-loader">
                <div class="uv-loader-orbit"></div>
                <div class="uv-loader-orbit"></div>
                <div class="uv-loader-orbit"></div>
            </div>
            <p class="uv-loader-text">Loading Staff Portal</p>
        </div>
    </main>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Role Switcher (For Staff Demonstration Purposes) -->
    <div class="role-switcher-container">
        <label for="demo-role-switcher">Switch Staff Role:</label>
        <select id="demo-role-switcher">
            <option value="worker" selected>Worker (Staff)</option>
            <option value="admin">Admin</option>
        </select>
        <small style="display: block; margin-top: 5px; opacity: 0.8; font-size: 0.6rem;">*Staff MVC Testing Mode</small>
    </div>

    <!-- Scripts -->
    <script src="https://js.puter.com/v2/"></script>
    <script type="module" src="app.js"></script>
    <script src="../assets/js/glass-animations.js"></script>
</body>
</html>
