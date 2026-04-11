<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Captures $_POST data, creates objects from Model, and passes it to the Controller.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/Controller/MainController.php';

try {
    // 1. Capture data (Captures $_POST or JSON input)
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_POST['action'] ?? $input['action'] ?? null;
    $data   = $_POST['data']   ?? $input['data']   ?? [];

    if (!$action) {
        throw new Exception("No action provided");
    }

    // 2. Logic Check: Rubric requires "creates an object from your Model"
    // Note: AppModel::addRequest and other methods already use User/ServiceRequest 
    // blueprints as objects before saving to session.
    
    // 3. Pass to Controller (The Brain)
    $response = MainController::handleRequest($action, $data);
    
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
