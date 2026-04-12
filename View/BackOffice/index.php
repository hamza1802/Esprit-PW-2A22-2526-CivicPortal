<?php
/**
 * BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Staff Portal</title>
    <meta name="description" content="Administrative and Worker functionalities for CivicPortal.">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <div style="padding: 100px; text-align: center;">
            <p>Loading CivicPortal Back Office...</p>
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
    <script type="module" src="app.js"></script>
</body>
</html>
