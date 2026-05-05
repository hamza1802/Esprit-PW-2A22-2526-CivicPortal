<?php
/**
 * End-to-end test: simulates what the browser does when editing a program.
 * Sends a multipart/form-data POST to Verification.php exactly like fetch() would.
 */

require_once __DIR__ . '/../Model/Database.php';

// 1. Get a program to update
$db = Database::getInstance()->getConnection();
$prog = $db->query("SELECT * FROM program WHERE status != 'cancelled' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$prog) { die("No programs in DB.\n"); }

echo "=== Testing program update (ID={$prog['id']}) ===\n\n";

// 2. Simulate the multipart POST exactly as the browser sends it
$boundary = '----TestBoundary' . time();
$body = '';

$fields = [
    'action'      => 'update_program',
    'id'          => $prog['id'],
    'title'       => $prog['title'],
    'description' => $prog['description'],
    'category'    => $prog['category'],
    'capacity'    => $prog['capacity'],
    'location'    => $prog['location'],
    'status'      => $prog['status'],
];

foreach ($fields as $key => $val) {
    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
    $body .= "$val\r\n";
}
$body .= "--$boundary--\r\n";

// 3. cURL to Verification.php (localhost)
$ch = curl_init('http://localhost/CivicPortal/Verification.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "Content-Type: multipart/form-data; boundary=$boundary",
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
if ($curlErr) echo "cURL Error: $curlErr\n";

echo "Response Body:\n$response\n\n";

// 4. Parse and analyze
$json = json_decode($response, true);
if ($json === null) {
    echo "!!! RESPONSE IS NOT VALID JSON !!!\n";
    echo "json_last_error_msg: " . json_last_error_msg() . "\n";
} else {
    echo "Parsed JSON:\n";
    echo "  success: " . var_export($json['success'] ?? null, true) . "\n";
    echo "  data:    " . var_export($json['data'] ?? null, true) . "\n";
    echo "  error:   " . var_export($json['error'] ?? null, true) . "\n";
}
?>
