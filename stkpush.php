<?php
require_once 'config.php';  // Load secret keys from config.php

$phone = $_POST['phone'] ?? null;
$amount = $_POST['amount'] ?? null;

if (!$phone || !$amount) {
    die("Phone number and amount are required.");
}

$accessTokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode("$consumerKey:$consumerSecret");

// Get access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $accessTokenUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $credentials"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response);
$accessToken = $result->access_token ?? null;

if (!$accessToken) {
    die("Failed to get access token");
}

// Prepare STK Push
$timestamp = date('YmdHis');
$password = base64_encode($businessShortCode . $passkey . $timestamp);

$stkPushUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$stkPushPayload = [
    'BusinessShortCode' => $businessShortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => intval($amount),
    'PartyA' => $phone,
    'PartyB' => $businessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => 'https://example.com/callback', // Use your real callback URL here
    'AccountReference' => 'Test123',
    'TransactionDesc' => 'Test Payment'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $stkPushUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkPushPayload));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
    echo "<p style='color: green; text-align: center;'>STK Push sent successfully! CheckoutRequestID: " . htmlspecialchars($result['CheckoutRequestID']) . "</p>";
} else {
    echo "<p style='color: red; text-align: center;'>Error: " . htmlspecialchars($result['errorMessage'] ?? 'Unknown error') . "</p>";
}
?>
