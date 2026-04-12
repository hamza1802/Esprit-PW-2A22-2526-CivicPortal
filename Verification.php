<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Handles both JSON requests and multipart file uploads.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/Controller/MainController.php';

try {
    // Determine if this is a file upload (multipart) or JSON request
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'multipart/form-data') !== false) {
        // ── File Upload Handler ──────────────────────────────────
        $action    = $_POST['action'] ?? null;
        $requestId = isset($_POST['requestId']) ? (int)$_POST['requestId'] : null;
        $docType   = $_POST['docType'] ?? 'other';
        $docId     = isset($_POST['docId']) ? (int)$_POST['docId'] : null;

        if (!$action) throw new Exception("No action provided");

        // Ensure uploads directory exists
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Ensure default users exist
        AppModel::ensureDefaultUser();

        if ($action === 'upload_files') {
            // Handle multiple file uploads
            if (empty($_FILES['files'])) {
                throw new Exception("No files provided.");
            }

            $results = [];
            $files = $_FILES['files'];
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;

            // Normalize single file to array format
            if (!is_array($files['name'])) {
                $files = [
                    'name'     => [$files['name']],
                    'type'     => [$files['type']],
                    'tmp_name' => [$files['tmp_name']],
                    'error'    => [$files['error']],
                    'size'     => [$files['size']],
                ];
                $fileCount = 1;
            }

            // Also get per-file docType if sent as array
            $docTypes = $_POST['docTypes'] ?? [];
            if (!is_array($docTypes)) {
                $docTypes = array_fill(0, $fileCount, $docType);
            }

            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue; // skip failed uploads
                }

                $originalName = basename($files['name'][$i]);
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeName = 'doc_' . $requestId . '_' . time() . '_' . $i . '.' . $ext;

                if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $safeName)) {
                    $thisType = $docTypes[$i] ?? 'other';
                    $doc = AppModel::addDocument($requestId, $safeName, $thisType);
                    $results[] = $doc;
                }
            }

            echo json_encode(['success' => true, 'data' => $results]);
            exit;

        } else if ($action === 'replace_file') {
            // Handle single file replacement
            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("No file provided or upload error.");
            }

            $originalName = basename($_FILES['file']['name']);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = 'doc_' . $requestId . '_' . time() . '.' . $ext;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $safeName)) {
                throw new Exception("Failed to save uploaded file.");
            }

            $result = AppModel::updateDocument($docId, $safeName, $docType);
            echo json_encode(['success' => true, 'data' => $result]);
            exit;
        }

        throw new Exception("Unknown upload action: " . $action);

    } else {
        // ── Standard JSON Handler ────────────────────────────────
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $_POST['action'] ?? $input['action'] ?? null;
        $data   = $_POST['data']   ?? $input['data']   ?? [];

        if (!$action) {
            throw new Exception("No action provided");
        }

        if ($action === 'reset_session') {
            session_start();
            session_destroy();
            echo json_encode(['success' => true, 'data' => 'Session reset.']);
            exit;
        }

        $response = MainController::handleRequest($action, $data);
        echo json_encode(['success' => true, 'data' => $response]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
