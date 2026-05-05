<?php
$data = [
    'action' => 'update_program',
    'id' => 1,
    'title' => 'Test Program Edit',
    'description' => 'This is a test program description that has more than 20 characters.',
    'category' => 'Sports',
    'capacity' => '50',
    'location' => 'Test Location',
    'status' => 'active'
];

$ch = curl_init('http://localhost/CivicPortal/Verification.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
