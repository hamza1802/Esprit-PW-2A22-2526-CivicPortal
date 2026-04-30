<?php
/**
 * View/BackOffice/index.php
 * Main entry point for CivicPortal Staff Portal.
 * Simplified Sidebar: ONLY USERS.
 */
require_once __DIR__ . '/../../Model/AppModel.php';
require_once __DIR__ . '/../../Controller/MainController.php';
require_once __DIR__ . '/../../controller/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Check (Session should already be started in root)
$session_role = $_SESSION['user_role'] ?? '';
if (!in_array($session_role, ['admin', 'worker', 'agent'])) {
    header('Location: index.php?page=front_home');
    exit;
}

// Map 'agent' to 'worker' for UI consistency
$role = ($session_role === 'agent') ? 'worker' : $session_role;

// Refresh Data from Backend
AppModel::init();
$data = MainController::showData();

// Get current user details
$userId = $_SESSION['user_id'] ?? 0;
$userObj = UserController::getUserById((int)$userId);
$userName = $userObj ? $userObj->getDisplayName() : ($_SESSION['user_name'] ?? 'Staff Member');

// 3. Routing (Tab-based)
$tab = $_GET['tab'] ?? 'users';
if (!in_array($tab, ['users', 'statistic'])) {
    $tab = 'users';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal | Staff Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="View/assets/css/style.css">
    <link rel="stylesheet" href="View/assets/css/admin.css">
    <style>
        .user-role-badge { 
            background: var(--primary-red) !important; 
            color: white !important; 
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 900;
        }
        .reveal { opacity: 1 !important; transform: none !important; }
        
        .nav-links a.active {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
        }
    </style>
</head>
<body class="reveal-content" style="display: flex; min-height: 100vh; margin: 0; background: var(--bg-neutral);">

    <!-- Sidebar Navigation - REDUCED TO ONLY USERS -->
    <nav style="width: 280px; flex-shrink: 0; background: var(--primary-navy); height: 100vh; position: sticky; top: 0; z-index: 100;">
        <div class="nav-brand" style="padding: 2.5rem 1.5rem; color: white; font-weight: 900; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem;">
            CIVICPORTAL<br/>STAFF
        </div>
        <ul class="nav-links" style="list-style: none; padding: 0 1rem;">
            <?php if ($role === 'admin'): ?>
                <li style="margin-bottom: 1rem;">
                    <a href="index.php?page=back_dashboard&tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 1rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; transition: 0.3s; text-transform: uppercase;">
                        <i class="bi bi-people" style="font-size: 1.2rem;"></i> USERS
                    </a>
                </li>
                <li style="margin-bottom: 1rem;">
                    <a href="index.php?page=back_dashboard&tab=statistic" class="<?= $tab === 'statistic' ? 'active' : '' ?>" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 1rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; transition: 0.3s; text-transform: uppercase;">
                        <i class="bi bi-graph-up-arrow" style="font-size: 1.2rem;"></i> STATISTIC
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="user-controls" style="margin-top: auto; padding: 2.5rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <div style="display:flex; flex-direction:column; gap:12px; width:100%;">
                <div class="user-role-badge" style="text-align: center; text-transform: uppercase;"><?= $role ?></div>
                <a href="index.php?page=front_home" class="btn btn-small" style="background:white; color:var(--primary-navy); font-weight: 900; text-align:center; border: none; font-size: 0.75rem; border-radius: 8px;">FRONTOFFICE</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main id="app" style="flex-grow: 1; height: 100vh; overflow-y: auto; padding: 3rem;">
        <?php if ($tab === 'users'): ?>
            <?php include __DIR__ . '/users_list_partial.php'; ?>
        <?php elseif ($tab === 'statistic'): ?>
            <?php include __DIR__ . '/stats_partial.php'; ?>
        <?php endif; ?>
    </main>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <script src="View/assets/js/glass-animations.js"></script>
    <script>
        function showToast(message, isError = false) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isError ? 'var(--primary-red)' : 'var(--primary-navy)';
            toast.style.color = 'white';
            toast.style.padding = '1.5rem 3rem';
            toast.style.borderRadius = '8px';
            toast.style.marginBottom = '1.2rem';
            toast.style.boxShadow = '0 15px 40px rgba(0,0,0,0.15)';
            toast.style.fontWeight = '900';
            toast.style.letterSpacing = '1px';
            toast.style.fontSize = '0.85rem';
            toast.innerText = message.toUpperCase();
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }
        window.showToast = showToast;
    </script>
</body>
</html>