<?php
/**
 * FrontOffice/index.php
 * Main entry point for CivicPortal Citizen Portal
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$currentProfile = null;
$userModel = null;
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../Model/Profile.php';
    require_once __DIR__ . '/../../Controller/UserController.php';
    $currentProfile = UserController::getProfileByUserId((int)$_SESSION['user_id']);
    $userModel = UserController::getUserById((int)$_SESSION['user_id']);
}

$currentUser = [
    'id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['user_name'] ?? 'Guest',
    'email' => $_SESSION['user_email'] ?? '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'bio' => $currentProfile ? $currentProfile->getBio() : '',
    'phoneNumber' => $currentProfile ? $currentProfile->getPhoneNumber() : '',
    'dateOfBirth' => $currentProfile ? $currentProfile->getDateOfBirth() : '',
    'avatar' => $currentProfile ? $currentProfile->getAvatarUrl() : '',
    'two_fa_enabled' => $userModel ? $userModel->isTwoFaEnabled() : false
];

$successMsg = $_SESSION['success'] ?? '';
$errorsMsg = $_SESSION['errors'] ?? [];
unset($_SESSION['success'], $_SESSION['errors']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Citizen Services</title>
    <meta name="description" content="Access municipal services, programs, and submit requests online through CivicPortal.">
    <base href="/a1/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

    <script>
        window.SERVER_USER = <?= json_encode($currentUser, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        window.SERVER_MESSAGES = {
            success: <?= json_encode($successMsg) ?>,
            errors: <?= json_encode($errorsMsg) ?>
        };
    </script>

    <!-- Scripts -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script type="module" src="View/FrontOffice/app.js"></script>
    <script src="View/assets/js/glass-animations.js"></script>
</body>
</html>
