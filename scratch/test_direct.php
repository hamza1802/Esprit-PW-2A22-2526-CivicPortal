<?php
/**
 * Diagnostic: trace what Verification.php actually receives from different request types.
 * This bypasses cURL and directly emulates $_POST + $_FILES like the browser does.
 */

// Simulate the exact data the form sends
echo "=== Direct PHP test: what does MainController see? ===\n\n";

require_once __DIR__ . '/../Controller/MainController.php';

$data = [
    'action'      => 'update_program',
    'id'          => '104',
    'title'       => 'Community Garden Workshop',
    'description' => 'A hands-on workshop teaching residents how to grow organic vegetables.',
    'category'    => 'Environment',
    'capacity'    => '30',
    'location'    => 'Central Park',
    'status'      => 'active',
];

try {
    $action = $data['action'];
    unset($data['action']); // Verification.php uses $data = $_POST which still has 'action'
    
    echo "Action: $action\n";
    echo "Data keys: " . implode(', ', array_keys($data)) . "\n\n";
    
    $response = MainController::handleRequest($action, $data);
    echo "Response: " . var_export($response, true) . "\n";
    echo "\nSUCCESS\n";
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>
