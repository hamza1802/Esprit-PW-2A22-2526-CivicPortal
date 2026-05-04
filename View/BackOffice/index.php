<?php
/**
 * BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guard: Only admin and agent roles can access BackOffice
if (!isset($_SESSION['user_id'])) {
    header('Location: ../FrontOffice/login.php');
    exit;
}
if (!in_array($_SESSION['user_role'] ?? '', ['agent', 'admin'])) {
    header('Location: ../FrontOffice/index.php');
    exit;
}

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Mobile: hamburger toggle -->
    <button id="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>

    <!-- Mobile: backdrop overlay (closes sidebar on tap) -->
    <div class="backdrop" id="sidebar-backdrop"></div>

    <!-- Inject PHP Session State into JS Environment -->
    <script>
        window.SERVER_USER = {
            id: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
            name: <?= json_encode($_SESSION['user_name'] ?? 'Staff') ?>,
            email: <?= json_encode($_SESSION['user_email'] ?? '') ?>,
            role: <?= json_encode($_SESSION['user_role'] ?? '') ?>
        };
    </script>

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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <script src="https://js.puter.com/v2/" defer></script>
    <script src="validate.js"></script>
    <script type="module" src="app.js"></script>
    <script src="../assets/js/glass-animations.js" defer></script>
    <script src="../assets/js/sidebar.js" defer></script>
</body>
</html>
