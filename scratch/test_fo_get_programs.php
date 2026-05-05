<?php
$ch = curl_init('http://localhost/CivicPortal/Verification.php');
$payload = json_encode(['action' => 'get_programs', 'data' => []]);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
]);
$response = curl_exec($ch);
echo "Response:\n";
var_dump($response);
?>
