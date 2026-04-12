<?php
/**
 * Verification.php
 * Entry point for all form/API actions.
 * Upgraded to handle both JSON (fetch) and Multipart (File Uploads) requests.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/Controller/MainController.php';

try {
    // 1. Determine Input Method
    $action = null;
    $data   = [];

    // Check if it is a JSON request
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if ($jsonInput && isset($jsonInput['action'])) {
        $action = $jsonInput['action'];
        $data   = $jsonInput['data'] ?? [];
    } 
    // Otherwise, check for standard POST (Multipart or Form-urlencoded)
    else if (isset($_POST['action'])) {
        $action = $_POST['action'];
        // For standard POST, data might be flattened or in a 'data' prefix
        // We handle the 'data' key specifically if provided, else take all $_POST
        if (isset($_POST['data']) && is_array($_POST['data'])) {
            $data = $_POST['data'];
        } else {
            $data = $_POST; // Merge all post fields
        }

        // 2. Wrap Files if any
        foreach($_FILES as $key => $file) {
            $data[$key . '_file'] = $file;
        }
    }

    if (!$action) {
        throw new Exception("No action provided");
    }

    // 3. Pass to Controller
    $response = MainController::handleRequest($action, $data);
    
    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
