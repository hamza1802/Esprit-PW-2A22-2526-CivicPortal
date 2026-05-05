<?php
/**
 * Simulation: trace exactly what Verification.php does with multipart FormData
 * that has flat keys (no nested 'data' sub-array).
 */

// Simulate $_POST as the browser would set it from FormData
$_POST = [
    'action'      => 'update_program',
    'id'          => '104',
    'title'       => 'Community Garden Workshop',
    'description' => 'A hands-on workshop teaching residents how to grow organic vegetables in their backyard.',
    'category'    => 'Environment',
    'capacity'    => '30',
    'location'    => 'Central Park',
    'status'      => 'active',
];
$_FILES = [];

// Run through the EXACT same logic as Verification.php
$action = null;
$data   = [];

$jsonInput = json_decode(file_get_contents('php://input'), true);
// In a real multipart POST, php://input is empty, so $jsonInput will be null/false.
// Simulate that:
$jsonInput = null;

if ($jsonInput && isset($jsonInput['action'])) {
    $action = $jsonInput['action'];
    $data   = $jsonInput['data'] ?? [];
} else if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if (isset($_POST['data']) && is_array($_POST['data'])) {
        $data = $_POST['data'];
    } else {
        $data = $_POST; // <-- This is what happens!
    }
    foreach($_FILES as $key => $file) {
        $data[$key . '_file'] = $file;
    }
}

echo "Action: " . var_export($action, true) . "\n";
echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
echo "Data['id'] = " . var_export($data['id'] ?? 'MISSING', true) . "\n";
echo "Data['action'] = " . var_export($data['action'] ?? 'MISSING', true) . "\n";
echo "Data['status'] = " . var_export($data['status'] ?? 'MISSING', true) . "\n\n";

// Now pass to MainController
require_once __DIR__ . '/../Controller/MainController.php';

try {
    $response = MainController::handleRequest($action, $data);
    echo "Response: " . var_export($response, true) . "\n";
    
    if ($response === false) {
        echo "\n!!! RESPONSE IS FALSE — Verification.php would throw !!!\n";
    } else {
        echo "\nOK — would return {success: true, data: true}\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
?>
