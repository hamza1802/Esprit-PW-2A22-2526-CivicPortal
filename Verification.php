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
