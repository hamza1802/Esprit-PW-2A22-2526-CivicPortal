<?php
/**
 * debug_api_root.php
 * Simulates an enrollment request to see the raw output of Verification.php.
 * Placed in root to avoid path issues.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['action'] = 'enroll_user';
    $_POST['userId'] = 1;
    $_POST['programId'] = 101;

    // Capture output
    ob_start();
    require_once 'Verification.php';
    $output = ob_get_clean();

    echo "RAW OUTPUT:\n";
    echo $output;

} catch (Exception $e) {
    echo "DEBUG ERROR: " . $e->getMessage();
}
?>
