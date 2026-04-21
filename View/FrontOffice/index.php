<?php
/**
 * FrontOffice/index.php
 * Main entry point for CivicPortal Citizen Portal
 */
require_once '../../Model/AppModel.php';
AppModel::init();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Citizen Services</title>
    <meta name="description" content="Access municipal services, programs, and submit requests online through CivicPortal.">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    
    <!-- Leaflet CSS & JS for Mapping -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        /* Dark-mode Leaflet tile filter */
        .leaflet-tile { filter: brightness(0.5) invert(1) contrast(1.4) hue-rotate(180deg) saturate(0.6); }
        .leaflet-container { background: var(--bg-main) !important; border-radius: var(--radius-lg); }
        .leaflet-popup-content-wrapper {
            background: rgba(15,15,35,0.95) !important;
            border: 1px solid rgba(255,255,255,0.12) !important;
            border-radius: 12px !important;
            color: #f0f0ff !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5) !important;
        }
        .leaflet-popup-tip { background: rgba(15,15,35,0.95) !important; }
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
            <p>Loading CivicPortal Front Office...</p>
        </div>
    </main>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Scripts -->
    <script type="module" src="app.js"></script>
</body>
</html>
