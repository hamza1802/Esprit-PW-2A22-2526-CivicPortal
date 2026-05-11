<?php
ob_start();
session_start();
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/Controller/UserController.php';
ob_clean();
header('Content-Type: application/json');

$PY = 'http://localhost:5001';

/**
 * Ensures the Python Face ID service is running.
 * If not, it attempts to launch it in the background.
 */
function ensure_service_running() {
    $host = '127.0.0.1';
    $port = 5001;
    $connection = @fsockopen($host, $port, $errno, $errstr, 0.1);
    if (!$connection) {
        // Service is down, launch it in the background (Windows specific)
        $root = __DIR__;
        $cmd = "cd /d \"$root\" && start /B python face_service/app.py";
        pclose(popen($cmd, "r"));
        // Give it a moment to initialize
        usleep(800000); // 0.8s
    } else {
        fclose($connection);
    }
}


$raw    = file_get_contents('php://input');
$body   = $raw ? (json_decode($raw, true) ?? []) : [];
$action = $body['action'] ?? ($_POST['action'] ?? '');

// ── enroll ──────────────────────────────────────────────────────────────────
if ($action === 'enroll') {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to enroll a face']);
        exit;
    }
    $desc = $body['face_descriptor'] ?? null;
    if (!$desc || !is_array($desc)) {
        echo json_encode(['success' => false, 'message' => 'No face descriptor provided']);
        exit;
    }
    echo py('/enroll', ['user_id' => (int)$_SESSION['user_id'], 'face_descriptor' => $desc]);
    exit;
}

// ── verify ──────────────────────────────────────────────────────────────────
if ($action === 'verify') {
    $email = trim($body['email'] ?? '');
    $desc  = $body['face_descriptor'] ?? null;

    if (!$email || !$desc || !is_array($desc)) {
        echo json_encode(['match' => false, 'message' => 'Email and face data are required']);
        exit;
    }

    $user = UserController::getUserByEmail($email);
    if (!$user) {
        echo json_encode(['match' => false, 'message' => 'No account found with that email address']);
        exit;
    }

    $resp_raw  = py('/verify', ['user_id' => $user->getId(), 'face_descriptor' => $desc]);
    $resp      = json_decode($resp_raw, true);

    if ($resp && !empty($resp['match'])) {
        $_SESSION['user_id']    = $user->getId();
        $_SESSION['user_name']  = $user->getDisplayName();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role']  = $user->getRole();
        UserController::ensureProfileExists($user->getId());
        echo json_encode(['match' => true, 'redirect' => 'index.php']);
    } else {
        echo $resp_raw;
    }
    exit;
}

// ── disable ─────────────────────────────────────────────────────────────────
if ($action === 'disable') {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    echo py('/disable', ['user_id' => (int)$_SESSION['user_id']]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);

// ── helper: call Python service, always return valid JSON string ─────────────
function py(string $path, array $data): string {
    global $PY;
    ensure_service_running();
    $ch = curl_init($PY . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $result  = curl_exec($ch);
    $errno   = curl_errno($ch);
    curl_close($ch);

    if ($errno || !$result) {
        return json_encode(['success' => false, 'match' => false,
            'message' => 'Face service is unavailable — please use password login']);
    }
    if (json_decode($result) === null) {
        return json_encode(['success' => false, 'match' => false,
            'message' => 'Face service returned an unexpected response']);
    }
    return $result;
}
