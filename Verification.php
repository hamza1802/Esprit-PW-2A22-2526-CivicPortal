<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Captures $_POST data, creates objects from Model, and passes it to the Controller.
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/Controller/MainController.php';

// Security Check: Only logged-in users can access these APIs
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

try {
    // 1. Capture data (Captures $_POST or JSON input)
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_POST['action'] ?? $input['action'] ?? null;
    $data   = $_POST['data']   ?? $input['data']   ?? [];

    if (!$action) {
        throw new Exception("No action provided");
    }

    // 2. Logic Check: Pass to Controller (The Brain)
    $response = MainController::handleRequest($action, $data);
    
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
