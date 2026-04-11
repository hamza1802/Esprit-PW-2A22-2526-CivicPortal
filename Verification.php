<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Captures $_POST data, creates objects from Model, passes to Controller.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/Controller/MainController.php';

try {
    // Support both JSON payloads (SPA fetch) and traditional $_POST forms
    $input = json_decode(file_get_contents('php://input'), true);

    $action = $_POST['action'] ?? $_GET['action'] ?? $input['action'] ?? null;
    $data   = $_POST['data']   ?? $input['data']   ?? [];

    if (!$action) {
        throw new Exception("No action provided");
    }

    $response = MainController::handleRequest($action, $data);
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
