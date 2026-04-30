<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

require_once __DIR__ . '/Model/AppModel.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/Model/Profile.php';

AppModel::init();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$old = $_SESSION['old'] ?? [];

$page = $_GET['page'] ?? null;
$tab = $_GET['tab'] ?? null;
if ($page === null) {
    $page = 'front_home';
}
unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

$currentUser = null;
$currentProfile = null;
$currentUserAvatar = null;
if (!empty($_SESSION['user_id'])) {
    $currentUser = UserController::getUserById((int)$_SESSION['user_id']);
    // If user doesn't exist (was deleted), clear session and redirect to login
    if ($currentUser === null) {
        session_destroy();
        header('Location: index.php?page=front_login');
        exit;
    }
    $currentProfile = UserController::ensureProfileExists((int)$_SESSION['user_id']);
    // Only set avatar if profile exists
    if ($currentProfile) {
        $currentUserAvatar = $_SESSION['user_avatar'] ?? $currentProfile->getAvatarUrl();
        if (!empty($currentProfile->getAvatarUrl())) {
            $currentUserAvatar = $currentProfile->getAvatarUrl();
            $_SESSION['user_avatar'] = $currentUserAvatar;
        }
    }
}

$friends = AppModel::getFriends();
$friendSearch = trim($_GET['friend_search'] ?? '');
if ($friendSearch !== '') {
    $friends = array_values(array_filter($friends, function ($friend) use ($friendSearch) {
        $needle = mb_strtolower($friendSearch);
        return mb_strpos(mb_strtolower($friend['name']), $needle) !== false || mb_strpos(mb_strtolower($friend['email']), $needle) !== false;
    }));
}

$protectedRoutes = ['front_profile', 'back_dashboard'];
if (in_array($page, $protectedRoutes, true) && (empty($_SESSION['user_id']) || $currentUser === null)) {
    header('Location: index.php?page=front_login');
    exit;
}

// Backoffice access control - ONLY admin (agent and others are blocked)
if (strpos($page, 'back_') === 0 && ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.php?page=front_home');
    exit;
}

if ($action === 'logout') {
    UserController::logout();
    header('Location: index.php?page=front_home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'login':
            $result = UserController::login($_POST);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=front_login');
                exit;
            }
            if (!empty($result['requires_2fa'])) {
                header('Location: index.php?page=front_verify_otp');
                exit;
            }
            header('Location: index.php?page=front_home');
            exit;

        case 'verify_otp':
            if (empty($_SESSION['pending_2fa_user_id'])) {
                header('Location: index.php?page=front_login');
                exit;
            }
            $userId = (int)$_SESSION['pending_2fa_user_id'];
            $code = $_POST['otp_code'] ?? '';
            
            if (UserController::verifyOtp($userId, $code)) {
                $user = UserController::getUserById($userId);
                unset($_SESSION['pending_2fa_user_id']);
                
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_name'] = $user->getDisplayName();
                $_SESSION['user_email'] = $user->getEmail();
                $_SESSION['user_role'] = $user->getRole();
                UserController::ensureProfileExists($user->getId());
                
                header('Location: index.php?page=front_home');
                exit;
            } else {
                $_SESSION['errors'] = ['otp' => 'Code OTP invalide ou expiré.'];
                header('Location: index.php?page=front_verify_otp');
                exit;
            }

        case 'register':
            $result = UserController::register($_POST);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=front_register');
                exit;
            }
            $_SESSION['success'] = 'Registration successful. You can now log in.';
            header('Location: index.php?page=front_login');
            exit;

        case 'update_profile':
            if (empty($_SESSION['user_id'])) {
                header('Location: index.php?page=front_login');
                exit;
            }

            if (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                $uploadDir = __DIR__ . '/View/assets/images';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $fileName = hash('sha256', $_SESSION['user_id'] . microtime()) . '.' . $extension;
                $destination = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                    $rawPath = 'View/assets/images/' . $fileName;
                    // Mask as a password-like hash: $2y$10$ + base64(path)
                    $_SESSION['user_avatar'] = '$2y$10$' . base64_encode($rawPath);
                }
            }

            $profileData = $_POST;
            $profileData['two_fa_enabled'] = isset($_POST['two_fa_enabled']) ? 1 : 0;
            if (!empty($_SESSION['user_avatar'])) {
                $profileData['avatar_url'] = $_SESSION['user_avatar'];
            }

            $result = UserController::updateProfile((int)$_SESSION['user_id'], $profileData);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=front_profile');
                exit;
            }
            $_SESSION['success'] = $result['success'];
            header('Location: index.php?page=front_profile');
            exit;

        case 'add_friend':
            AppModel::addFriend($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['role'] ?? 'citizen');
            $_SESSION['success'] = 'Friend added successfully.';
            header('Location: index.php?page=front_profile');
            exit;

        case 'remove_friend':
            $friendId = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
            if (AppModel::removeFriend($friendId)) {
                $_SESSION['success'] = 'Friend removed successfully.';
            }
            header('Location: index.php?page=front_profile');
            exit;

        case 'update_status':
            // Only admin or worker can update status
            if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'worker', 'agent'])) {
                header('Location: index.php?page=front_home');
                exit;
            }
            $result = AppModel::updateRequestStatus($_POST['data']['id'], $_POST['data']['status']);
            $_SESSION['success'] = 'Status updated successfully.';
            header('Location: index.php?page=back_dashboard&tab=queue');
            exit;

        case 'get_user':
        case 'create_user':
        case 'update_user':
        case 'delete_user':
            // Robust check: only admin can perform these actions
            if (($_SESSION['user_role'] ?? '') !== 'admin') {
                $_SESSION['errors'] = ['Unauthorized action. Admin access required.'];
                header('Location: index.php');
                exit;
            }

            if ($action === 'get_user') {
                $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                $user = UserController::getUserById($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'id' => $user->getId(),
                    'name' => $user->getDisplayName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]);
                exit;
            }

            if ($action === 'create_user') {
                $result = UserController::createUser($_POST);
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    if (empty($result['errors']) && !empty($result['user_id'])) {
                        $user = UserController::getUserById((int)$result['user_id']);
                        $profile = UserController::getProfileByUserId($user->getId());
                        $result['user'] = [
                            'id' => $user->getId(),
                            'name' => $user->getDisplayName(),
                            'email' => $user->getEmail(),
                            'role' => $user->getRole(),
                            'avatar' => $profile ? $profile->getAvatarUrl() : null,
                            'created_at' => $user->getCreatedAt()
                        ];
                    }
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    exit;
                }
                if (!empty($result['errors'])) {
                    $_SESSION['errors'] = $result['errors'];
                    $_SESSION['old'] = $_POST;
                    header('Location: index.php?page=back_dashboard&tab=users');
                    exit;
                }
                $_SESSION['success'] = $result['success'];
                header('Location: index.php?page=back_dashboard&tab=users');
                exit;
            }

            if ($action === 'update_user') {
                $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                $result = UserController::updateProfile($userId, $_POST);
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    if (empty($result['errors'])) {
                        $user = UserController::getUserById($userId);
                        $profile = UserController::getProfileByUserId($user->getId());
                        $result['user'] = [
                            'id' => $user->getId(),
                            'name' => $user->getDisplayName(),
                            'email' => $user->getEmail(),
                            'role' => $user->getRole(),
                            'avatar' => $profile ? $profile->getAvatarUrl() : null,
                            'created_at' => $user->getCreatedAt()
                        ];
                    }
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    exit;
                }
                if (!empty($result['errors'])) {
                    $_SESSION['errors'] = $result['errors'];
                    $_SESSION['old'] = $_POST;
                    header('Location: index.php?page=back_dashboard&tab=users&edit=' . $userId);
                    exit;
                }
                $_SESSION['success'] = $result['success'];
                header('Location: index.php?page=back_dashboard&tab=users');
                exit;
            }

            if ($action === 'delete_user') {
                $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                $result = UserController::deleteUser($userId);
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    exit;
                }

                if (!empty($result['errors'])) {
                    $_SESSION['errors'] = $result['errors'];
                } else {
                    $_SESSION['success'] = $result['success'];
                    if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
                        UserController::logout();
                        header('Location: index.php?page=front_home');
                        exit;
                    }
                }
                header('Location: index.php?page=back_dashboard&tab=users');
                exit;
            }
            break;
    }
}

$users = [];
$editingUser = null;
if ($page === 'back_dashboard' && $tab === 'users') {
    $users = UserController::getAllUsers();
    if (isset($_GET['edit'])) {
        $editingUser = UserController::getUserById((int)$_GET['edit']);
    }
}

switch ($page) {
    case 'front_register':
        include __DIR__ . '/View/FrontOffice/register.php';
        break;
    case 'front_profile':
        header('Location: View/FrontOffice/index.php#profile');
        exit;
    case 'front_home':
        if ($currentUser !== null) {
            $currentUserArray = [
                'id' => $currentUser->getId(),
                'name' => $currentUser->getDisplayName(),
                'email' => $currentUser->getEmail(),
                'role' => $currentUser->getRole()
            ];
            $currentUser = $currentUserArray;
        } else {
            $currentUser = [
                'id' => null,
                'name' => 'Guest',
                'email' => '',
                'role' => 'guest'
            ];
        }
        include __DIR__ . '/View/FrontOffice/index.php';
        break;
    case 'back_dashboard':
        include __DIR__ . '/View/BackOffice/index.php';
        break;
    case 'front_verify_otp':
        if (empty($_SESSION['pending_2fa_user_id'])) {
            header('Location: index.php?page=front_login');
            exit;
        }
        include __DIR__ . '/View/FrontOffice/verify_otp.php';
        break;
    case 'front_login':
    default:
        include __DIR__ . '/View/FrontOffice/login.php';
        break;
}
