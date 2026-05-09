<?php
/**
 * Verification.php
 * Central JSON API router for all non-transport actions.
 *
 * SECURITY improvements over naive implementation:
 *  • bootstrap.php enforces HttpOnly/SameSite session cookies and disables display_errors.
 *  • Raw exception messages are logged server-side via error_log(); only a generic message
 *    is returned to the client. This prevents leaking table names, file paths, or stack traces.
 *  • APP_DEBUG constant from bootstrap allows detailed errors ONLY in development, never prod.
 */

require_once __DIR__ . '/bootstrap.php';
define('_CIVICPORTAL_BOOTSTRAP_', true);

header('Content-Type: application/json');

require_once __DIR__ . '/Controller/MainController.php';

try {
    $action = null;
    $data   = [];

    // JSON body (fetch API calls)
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if ($jsonInput && isset($jsonInput['action'])) {
        $action = $jsonInput['action'];
        $data   = $jsonInput['data'] ?? [];
    }
    // Standard multipart / form-urlencoded (file uploads)
    elseif (isset($_POST['action'])) {
        $action = $_POST['action'];
        $data   = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : $_POST;
        foreach ($_FILES as $key => $file) {
            $data[$key . '_file'] = $file;
        }
    }

    if (!$action) {
        throw new Exception('No action provided.');
    }

    // ── Multi-document upload helpers (Service Requests module) ──
    // These actions are only ever used with multipart/form-data and
    // operate on the documents table + the local /uploads/ directory.
    if (in_array($action, ['upload_files', 'replace_file'], true)) {
        if (empty($_SESSION['user_id'])) {
            throw new Exception('Unauthorized: authentication required.');
        }

        require_once __DIR__ . '/Model/AppModel.php';

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

        $requestId = (int)($_POST['requestId'] ?? 0);
        if ($requestId <= 0) throw new Exception('requestId is required.');

        $request = AppModel::getRequestById($requestId);
        if (!$request) throw new Exception('Request not found.');

        $role   = $_SESSION['user_role'] ?? '';
        $userId = (int)$_SESSION['user_id'];
        if ($role === 'citizen' && (int)$request['user_id'] !== $userId) {
            throw new Exception('Forbidden: not your request.');
        }

        $allowedTypes = ['identity', 'proof', 'photo', 'certificate', 'other'];
        $defaultType  = (string)($_POST['docType'] ?? 'other');
        if (!in_array($defaultType, $allowedTypes, true)) $defaultType = 'other';

        $maxBytes      = 6 * 1024 * 1024; // 6 MB / file
        $allowedExts   = ['pdf','png','jpg','jpeg','webp'];

        if ($action === 'upload_files') {
            if (empty($_FILES['files'])) throw new Exception('No files provided.');

            $files     = $_FILES['files'];
            $isArray   = is_array($files['name']);
            $fileCount = $isArray ? count($files['name']) : 1;
            if (!$isArray) {
                $files = [
                    'name'     => [$files['name']],
                    'type'     => [$files['type']],
                    'tmp_name' => [$files['tmp_name']],
                    'error'    => [$files['error']],
                    'size'     => [$files['size']],
                ];
            }

            $docTypes = $_POST['docTypes'] ?? [];
            if (!is_array($docTypes)) $docTypes = array_fill(0, $fileCount, $defaultType);

            $results = [];
            for ($i = 0; $i < $fileCount; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                if ((int)$files['size'][$i] > $maxBytes) continue;

                $original = basename((string)$files['name'][$i]);
                $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExts, true)) continue;

                $safeName = 'doc_' . $requestId . '_' . time() . '_' . $i . '.' . $ext;
                if (!move_uploaded_file($files['tmp_name'][$i], $uploadDir . $safeName)) continue;

                $thisType = (string)($docTypes[$i] ?? $defaultType);
                $results[] = AppModel::addDocument($requestId, $safeName, $thisType);
            }

            echo json_encode(['success' => true, 'data' => $results]);
            exit;
        }

        if ($action === 'replace_file') {
            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file provided.');
            }
            if ((int)$_FILES['file']['size'] > $maxBytes) {
                throw new Exception('File is too large (max 6 MB).');
            }
            $original = basename((string)$_FILES['file']['name']);
            $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                throw new Exception('Unsupported file type.');
            }
            $docId    = (int)($_POST['docId'] ?? 0);
            if ($docId <= 0) throw new Exception('docId is required.');

            $safeName = 'doc_' . $requestId . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $safeName)) {
                throw new Exception('Failed to save uploaded file.');
            }

            $type = (string)($_POST['docType'] ?? $defaultType);
            $row  = AppModel::updateDocument($docId, $safeName, $type);
            echo json_encode(['success' => true, 'data' => $row]);
            exit;
        }
    }

    $response = MainController::handleRequest($action, $data);
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    // SECURITY: log full detail server-side; return only a safe message to the client.
    error_log('[CivicPortal][Verification] ' . $e->getMessage()
        . ' | File: ' . $e->getFile() . ':' . $e->getLine());

    http_response_code(400);
    $clientMessage = APP_DEBUG ? $e->getMessage() : 'An error occurred. Please try again.';
    echo json_encode(['success' => false, 'error' => $clientMessage]);
}
?>
