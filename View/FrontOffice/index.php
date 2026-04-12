<?php
/**
 * FrontOffice/index.php
 * Main entry point for CivicPortal Citizen Portal
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? 'Utilisateur',
    'email' => $_SESSION['user_email'] ?? '',
    'role' => $_SESSION['user_role'] ?? 'citizen'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Citizen Services</title>
    <meta name="description" content="Access municipal services, programs, and submit requests online through CivicPortal.">
    <base href="/4/">
    <link rel="stylesheet" href="View/assets/css/style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <nav>
        <!-- Injected via view.js -->
    </nav>

    <!-- Main Content Area -->
    <main id="app">
        <!-- Dynamic content injected here -->
        <div style="padding: 100px; text-align: center;">
            <p>Loading CivicPortal Front Office...</p>
        </div>
    </main>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        window.SERVER_USER = <?= json_encode($currentUser, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

    <!-- Scripts -->
    <script type="module" src="View/FrontOffice/app.js"></script>
</body>
</html>
