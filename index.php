<?php
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '01711223344'; // আপনার নাম্বার দিন
$number = "+880" . substr($phone, -10);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.deeptoplay.com/v2/auth/login?country=BD&platform=web&language=en',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(["number" => $number]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'origin: https://www.deeptoplay.com'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    "target_number" => $number,
    "http_status_code" => $http_code,
    "curl_error" => $error ?: "No cURL error",
    "api_response" => json_decode($response, true) ?: $response
], JSON_PRETTY_PRINT);
?>
