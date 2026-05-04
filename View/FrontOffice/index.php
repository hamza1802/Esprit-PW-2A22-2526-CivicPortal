<?php
/**
 * FrontOffice/index.php
 * Main entry point for CivicPortal Citizen Portal.
 */
require_once '../../bootstrap.php';
define('_CIVICPORTAL_BOOTSTRAP_', true);

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Use session data directly - avoid blocking database calls before rendering HTML
// Profile data will be fetched via API if needed by the frontend
$currentUser = [
    'id'            => (int)$_SESSION['user_id'],
    'name'          => $_SESSION['user_name']  ?? 'Citizen',
    'email'         => $_SESSION['user_email'] ?? '',
    'role'          => $_SESSION['user_role']  ?? 'citizen',
    'bio'           => '',  // Will be loaded by frontend via API
    'phoneNumber'   => '',  // Will be loaded by frontend via API
    'dateOfBirth'   => '',  // Will be loaded by frontend via API
    'has_profile_pic' => false,  // Will be loaded by frontend via API
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Citizen Services</title>
    <meta name="description" content="Access municipal services, programs, and submit requests online through CivicPortal.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="aurora-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Inject PHP Session State into JS Environment -->
    <?php
        $sessSuccess = $_SESSION['successMessage'] ?? null;
        $sessErrors  = $_SESSION['errorMessage']   ?? null;
        unset($_SESSION['successMessage'], $_SESSION['errorMessage']);
    ?>
    <script>
        window.SERVER_USER = <?= json_encode($currentUser, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        window.SERVER_MESSAGES = {
            success: <?= json_encode($sessSuccess) ?>,
            errors: <?= json_encode($sessErrors) ?>
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
            <p class="uv-loader-text">Loading CivicPortal</p>
        </div>
    </main>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Leaflet (loaded before the module so initTicketMaps() finds L) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Scripts -->
    <script type="module" src="app.js"></script>
    <script src="../assets/js/glass-animations.js"></script>
</body>
</html>
