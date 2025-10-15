<?php
// pg/process-payment.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}

// Check if we have booking data
if (!isset($_SESSION['pending_booking'])) {
    die(show_error("No booking data found. Please start the booking process again."));
}
// print_r($_SESSION['pending_booking']); die;
// Array ( [apartment_id] => 8 [days] => 1 [total_cost] => 60100 [user_id] => 12 [user_email] => peterparker@example.com [check_in] => 2025-10-10T17:44 [check_out] => 2025-10-11T17:44 )
$booking = $_SESSION['pending_booking'];
$paystack_secret_key = SK_TEST;

// Initialize Paystack payment
$callback_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . 
               $_SERVER['HTTP_HOST'] . '/?page=payment-callback';
$callback_url = CALLBACK_URL;

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $booking['total_cost'] * 100,
        'email' => $booking['user_email'],
        'callback_url' => $callback_url,
        'metadata' => $booking
    ]),
    CURLOPT_HTTPHEADER => [
        "authorization: Bearer " . $paystack_secret_key,
        "content-type: application/json",
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die(show_error("Payment initialization failed: " . $err));
}

$result = json_decode($response);

if (!$result->status || empty($result->data->authorization_url)) {
    die(show_error("Payment initialization failed: " . ($result->message ?? 'Unknown error')));
}

// Redirect to Paystack
header("Location: " . $result->data->authorization_url);
exit;
?>