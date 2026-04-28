<?php
/**
 * BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal
 */
require_once '../../Model/AppModel.php';
AppModel::ensureDefaultUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Staff Portal</title>
    <meta name="description" content="Administrative and Worker functionalities for CivicPortal.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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

    <!-- Scripts -->
    <script type="module" src="app.js"></script>
</body>
</html>
