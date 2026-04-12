<?php
session_start();

require_once __DIR__ . '/Model/AppModel.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/Model/Profile.php';

AppModel::init();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$old = $_SESSION['old'] ?? [];

$page = $_GET['page'] ?? null;
if ($page === null) {
    $page = !empty($_SESSION['user_id']) ? 'front_home' : 'front_login';
}
unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

$currentUser = null;
$currentProfile = null;
$currentUserAvatar = null;
if (!empty($_SESSION['user_id'])) {
    $currentUser = UserController::getUserById((int)$_SESSION['user_id']);
    $currentProfile = Profile::createIfMissing((int)$_SESSION['user_id']);
    $currentUserAvatar = $_SESSION['user_avatar'] ?? $currentProfile->getAvatarUrl();
    if (!empty($currentProfile->getAvatarUrl())) {
        $currentUserAvatar = $currentProfile->getAvatarUrl();
        $_SESSION['user_avatar'] = $currentUserAvatar;
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

$protectedRoutes = ['front_profile', 'back_users_list'];
if (in_array($page, $protectedRoutes, true) && empty($_SESSION['user_id'])) {
    header('Location: index.php?page=front_login');
    exit;
}

if ($action === 'logout') {
    UserController::logout();
    header('Location: index.php?page=front_login');
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
            header('Location: index.php?page=front_home');
            exit;

        case 'register':
            $result = UserController::register($_POST);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=front_register');
                exit;
            }
            $_SESSION['success'] = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
            header('Location: index.php?page=front_login');
            exit;

        case 'update_profile':
            if (empty($_SESSION['user_id'])) {
                header('Location: index.php?page=front_login');
                exit;
            }

            if (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                $uploadDir = __DIR__ . '/assets/images/avatars';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $destination = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                    $_SESSION['user_avatar'] = 'assets/images/avatars/' . $fileName;
                }
            }

            $profileData = $_POST;
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
            $_SESSION['success'] = 'Ami ajouté avec succès.';
            header('Location: index.php?page=front_profile');
            exit;

        case 'remove_friend':
            $friendId = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
            if (AppModel::removeFriend($friendId)) {
                $_SESSION['success'] = 'Ami supprimé avec succès.';
            }
            header('Location: index.php?page=front_profile');
            exit;

        case 'create_user':
            $result = UserController::createUser($_POST);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=back_users_list');
                exit;
            }
            $_SESSION['success'] = $result['success'];
            header('Location: index.php?page=back_users_list');
            exit;

        case 'update_user':
            $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $result = UserController::updateProfile($userId, $_POST);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=back_users_list&edit=' . $userId);
                exit;
            }
            $_SESSION['success'] = $result['success'];
            header('Location: index.php?page=back_users_list');
            exit;

        case 'delete_user':
            $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $result = UserController::deleteUser($userId);
            if (!empty($result['errors'])) {
                $_SESSION['errors'] = $result['errors'];
            } else {
                $_SESSION['success'] = $result['success'];
            }
            header('Location: index.php?page=back_users_list');
            exit;
    }
}

$users = [];
$editingUser = null;
if ($page === 'back_users_list') {
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
        $currentUserArray = [
            'id' => $currentUser->getId(),
            'name' => $currentUser->getName(),
            'email' => $currentUser->getEmail(),
            'role' => $currentUser->getRole()
        ];
        $currentUser = $currentUserArray;
        include __DIR__ . '/View/FrontOffice/index.php';
        break;
    case 'back_users_list':
        include __DIR__ . '/View/BackOffice/users_list.php';
        break;
    case 'front_login':
    default:
        include __DIR__ . '/View/FrontOffice/login.php';
        break;
}
