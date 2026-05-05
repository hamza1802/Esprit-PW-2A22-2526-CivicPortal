<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Controller/UserController.php';

header('Content-Type: application/json');

$python_service_url = "http://localhost:5001";

$action = '';
$data = [];

// Handle JSON input
$raw_data = file_get_contents('php://input');
if ($raw_data) {
    $data = json_decode($raw_data, true);
    $action = $data['action'] ?? '';
}

// Fallback to POST if not JSON
if (!$action) {
    $action = $_POST['action'] ?? '';
    $data = $_POST;
}

if ($action === 'enroll') {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $descriptor = $data['face_descriptor'] ?? null;

    if (!$descriptor) {
        echo json_encode(['success' => false, 'message' => 'Descriptor missing']);
        exit;
    }

    $response = call_python_service($python_service_url . '/enroll', [
        'user_id' => $user_id,
        'face_descriptor' => $descriptor
    ]);

    echo $response;
    exit;
}

if ($action === 'verify') {
    $email = $data['email'] ?? '';
    $descriptor = $data['face_descriptor'] ?? null;

    if (!$email || !$descriptor) {
        echo json_encode(['match' => false, 'message' => 'Email or descriptor missing']);
        exit;
    }

    $user = UserController::getUserByEmail($email);
    if (!$user) {
        echo json_encode(['match' => false, 'message' => 'User not found']);
        exit;
    }

    $response_json = call_python_service($python_service_url . '/verify', [
        'user_id' => $user->getId(),
        'face_descriptor' => $descriptor
    ]);

    $response = json_decode($response_json, true);

    if ($response && ($response['match'] ?? false)) {
        // Successful match - set session
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        UserController::ensureProfileExists($user->getId());
        
        echo json_encode(['match' => true, 'redirect' => 'index.php?page=front_home']);
    } else {
        echo $response_json;
    }
    exit;
}

if ($action === 'disable') {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $response = call_python_service($python_service_url . '/disable', [
        'user_id' => $user_id
    ]);

    echo $response;
    exit;
}

function call_python_service($url, $data) {
    $ch = curl_init($url);
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['success' => false, 'message' => 'Python Service Error: ' . $error_msg]);
    }
    
    curl_close($ch);
    return $result;
}

echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
