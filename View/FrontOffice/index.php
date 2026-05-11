<?php
/**
 * FrontOffice/index.php
 * Main entry point for CivicPortal Citizen Portal.
 */
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Controller/UserController.php';
define('_CIVICPORTAL_BOOTSTRAP_', true);

$isGuest = empty($_SESSION['user_id']);

if (!$isGuest) {
    $realUser = UserController::getUserById((int)$_SESSION['user_id']);
    $profile = UserController::getProfileByUserId((int)$_SESSION['user_id']);

    $currentUser = [
        'id'              => (int)$_SESSION['user_id'],
        'name'            => $realUser ? $realUser->getDisplayName() : ($_SESSION['user_name'] ?? 'Citizen'),
        'email'           => $realUser ? $realUser->getEmail() : ($_SESSION['user_email'] ?? ''),
        'role'            => $realUser ? $realUser->getRole() : ($_SESSION['user_role'] ?? 'citizen'),
        'two_fa_enabled'  => $realUser ? $realUser->isTwoFaEnabled() : 0,
        'has_profile_pic' => $realUser ? $realUser->hasProfilePic() : false,
        'bio'             => $profile ? $profile->getBio() : '',
        'phone_number'    => $profile ? $profile->getPhoneNumber() : '',
        'date_of_birth'   => $profile ? $profile->getDateOfBirth() : '',
        'isGuest'         => false
    ];
} else {
    $currentUser = [
        'id'              => 0,
        'name'            => 'Guest',
        'email'           => '',
        'role'            => 'guest',
        'two_fa_enabled'  => 0,
        'has_profile_pic' => false,
        'bio'             => '',
        'phone_number'    => '',
        'date_of_birth'   => '',
        'isGuest'         => true
    ];
}
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
    <link rel="stylesheet" href="../assets/css/face-id.css">
    <?php $faceAuthUrl = dirname(dirname(dirname($_SERVER['PHP_SELF']))) . '/face_auth.php'; ?>
    <script>window.FACE_AUTH_URL = '<?= htmlspecialchars($faceAuthUrl) ?>';</script>
    <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <script defer src="../assets/js/face-enroll.js"></script>
</head>
<body class="<?= $isGuest ? 'guest-mode' : '' ?>">
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
