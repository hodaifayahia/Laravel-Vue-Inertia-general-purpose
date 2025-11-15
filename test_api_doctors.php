<?php
// Test if the API endpoint is working
$url = 'http://127.0.0.1:8000/api/doctors/public';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $httpcode . "\n";
echo "Response:\n";
echo $response . "\n";
?>
