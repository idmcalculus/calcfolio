<?php
// Test script to verify login endpoint
// Run with: php test-login.php

$url = 'https://calcfolio-api-dev.up.railway.app/admin/login';
$data = [
    'username' => 'test_username', // Replace with actual username
    'password' => 'test_password'  // Replace with actual password
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Origin: http://localhost:3000',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "HTTP Code: $httpCode\n";
echo "\nHeaders:\n$header\n";
echo "\nBody:\n$body\n";

curl_close($ch);