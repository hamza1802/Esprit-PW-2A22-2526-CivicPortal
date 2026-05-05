<?php
// Simulate the Verification.php request
$_SERVER['REQUEST_METHOD'] = 'POST';
$jsonInput = json_encode([
    'action' => 'register',
    'data' => [
        'name' => 'Test User',
        'email' => 'testuser' . time() . '@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
        'role' => 'citizen'
    ]
]);
file_put_contents('php://memory', $jsonInput); // Won't work with file_get_contents('php://input') directly in CLI easily

// Let's just include Verification.php but mock the input
// Actually, in CLI, php://input is empty. Let's do it via MainController
require_once __DIR__ . '/../Controller/MainController.php';

try {
    $data = [
        'name' => 'Test User',
        'email' => 'testuser' . time() . '@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
        'role' => 'citizen'
    ];
    $response = MainController::handleRequest('register', $data);
    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
