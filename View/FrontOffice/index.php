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

require_once '../../Model/User.php';
require_once '../../Model/Profile.php';
require_once '../../Controller/UserController.php';

$uid            = (int)$_SESSION['user_id'];
$currentProfile = UserController::getProfileByUserId($uid);
$currentUserObj = UserController::getUserById($uid);

$currentUser = [
    'id'            => $uid,
    'name'          => $_SESSION['user_name']  ?? 'Citizen',
    'email'         => $_SESSION['user_email'] ?? '',
    'role'          => $_SESSION['user_role']  ?? 'citizen',
    'bio'           => $currentProfile ? $currentProfile->getBio()         : '',
    'phoneNumber'   => $currentProfile ? $currentProfile->getPhoneNumber() : '',
    'dateOfBirth'   => $currentProfile ? $currentProfile->getDateOfBirth() : '',
    'has_profile_pic' => $currentUserObj ? $currentUserObj->hasProfilePic() : false,
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

    <!-- Inject PHP Session State into JS Environment -->
    <script>
        window.SERVER_USER = <?= json_encode($currentUser, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        window.SERVER_MESSAGES = {
            success: <?= json_encode($successMsg) ?>,
            errors: <?= json_encode($errorsMsg) ?>
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
