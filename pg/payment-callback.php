<?php
// pg/payment-callback.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}

function show_success($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

// Verify payment
if (!isset($_GET['reference'])) {
    die(show_error("No payment reference provided"));
}

$reference = $_GET['reference'];
$paystack_secret_key = SK_TEST;

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer " . $paystack_secret_key,
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die(show_error("Payment verification failed: " . $err));
}

$result = json_decode($response);

if (!$result->status || $result->data->status !== 'success') {
    die(show_error("Payment verification failed: " . ($result->message ?? 'Unknown error')));
}

// Payment successful - process booking
try {
    $conn = $GLOBALS['conn'];
    
    // Get booking data from metadata (fallback to session)
    $booking_data = $result->data->metadata ?? $_SESSION['pending_booking'] ?? null;
    
    if (!$booking_data) {
        throw new Exception("No booking data found");
    }

    // Prepare data
    $booking_data = (array)$booking_data;
    $user_id = $booking_data['user_id'] ?? null;
    $apartment_id = $booking_data['apartment_id'] ?? null;
    $days = $booking_data['days'] ?? null;
    $addons = $booking_data['addons'] ?? [];
    $total_cost = $booking_data['total_cost'] ?? null;
    
    // Calculate other values if not in metadata
    $apartment = get_data("service_apartments", "WHERE id=$apartment_id")[0];
    $listing_daily_charge = (float)$apartment['listing_daily_charge'];
    $service_charge = (float)$apartment['service_charge'];
    $total_daily_charge = $listing_daily_charge * $days;
    $addons_cost = 0;
    $addons_names = [];
    
    $all_addons = get_data('addons');
    foreach ($all_addons as $addon) {
        if (in_array($addon['id'], (array)$addons)) {
            $addons_cost += (float)$addon['price'];
            $addons_names[] = $addon['service'];
        }
    }
    
    $vat = $total_daily_charge * 0.075;
    $addons_str = implode(', ', $addons_names);

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings 
        (user_id, apartment_id, days, addons, total_daily_charge, caution_fee, addons_cost, vat, total_cost, payment_reference, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $bind_result = $stmt->bind_param("iiissdddds", 
        $user_id, 
        $apartment_id, 
        $days, 
        $addons_str, 
        $total_daily_charge, 
        $service_charge, 
        $addons_cost, 
        $vat, 
        $total_cost, 
        $reference
    );
    
    if (!$bind_result) {
        throw new Exception("Bind failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        // Clear session
        unset($_SESSION['pending_booking']);
        
        // Show success message
        echo show_success("Booking and payment successful! Reference: " . htmlspecialchars($reference));
        
        // Add link to view booking or return home
        echo '<div class="text-center mt-3">';
        echo '<a href="./" class="btn btn-primary">Return Home</a>';
        echo '</div>';
    } else {
        throw new Exception("No rows affected - booking not saved");
    }
    
    $stmt->close();
} catch (Exception $e) {
    die(show_error("Booking failed: " . $e->getMessage()));
}
?>