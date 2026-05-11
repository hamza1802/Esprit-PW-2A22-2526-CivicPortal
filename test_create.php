<?php
session_start();
$_SESSION['user_id'] = 3;
$_SESSION['user_role'] = 'admin';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
$_POST['action'] = 'create_user';
$_POST['name'] = 'Test User';
$_POST['email'] = 'testuser1778466271@example.com'; // Duplicate email from before
$_POST['password'] = 'Password123!';
$_POST['confirm_password'] = 'Password123!';
$_POST['role'] = 'citizen';

// Fake Verification.php parsing
$data = $_POST;
require 'Controller/MainController.php';

try {
    $response = MainController::handleRequest('create_user', $data);
    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
