<?php
session_start();

/**
 * Professional Security API
 * Returns secure codes for client-side Canvas rendering.
 * This bypasses PHP GD dependencies and ensures high-quality display.
 */

if (isset($_GET['get_code'])) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 5; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    $_SESSION['captcha_code'] = $code;
    
    header('Content-Type: application/json');
    echo json_encode(['code' => $code]);
    exit;
}
?>
