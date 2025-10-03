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
        unset($_SESSION['pending_booking']);
        // Show booking receipt
        echo '<div class="container py-5"><div class="card mx-auto" style="max-width:500px;">';
        echo '<div class="card-header bg-light text-secondary text-center">
        <img class="img-fluid" src="img/site-icon.png" alt="Icon" style="width: 30px; height: 30px;">
        <h4 class="">Booking Receipt</h4>
        </div>';
        echo '<div class="card-body">';
        echo '<p><strong>Payment Reference:</strong> ' . htmlspecialchars($reference) . '</p>';
        echo '<p><strong>Apartment:</strong> ' . htmlspecialchars($apartment['title']) . '</p>';
        echo '<p><strong>Address:</strong> ' . htmlspecialchars($apartment['address']) . '</p>';
        echo '<p><strong>Check-in:</strong> ' . htmlspecialchars($booking_data['check_in'] ?? '-') . '</p>';
        echo '<p><strong>Check-out:</strong> ' . htmlspecialchars($booking_data['check_out'] ?? '-') . '</p>';
        echo '<p><strong>Number of Days:</strong> ' . htmlspecialchars($days) . '</p>';
        echo '<hr>';
        echo '<p><strong>Daily Charge:</strong> ₦' . number_format($listing_daily_charge, 2) . '</p>';
        echo '<p><strong>Caution Fee:</strong> ₦' . number_format($service_charge, 2) . '</p>';
        echo '<p><strong>VAT (7.5%):</strong> ₦' . number_format($vat, 2) . '</p>';
        echo '<p><strong>Total Cost:</strong> <span class="fw-bold">₦' . number_format($total_cost, 2) . '</span></p>';
        echo '</div>';
        echo '<div class="card-footer text-center">';
        echo '<button class="btn btn-success me-2" onclick="printReceipt()">Download Receipt</button>';
        echo '<a href="./" class="btn btn-primary">Return Home</a>';
        echo '</div></div></div>';
        
        // Fixed JavaScript for printing
        echo '<script>
        function printReceipt() {
            // Create a clone of the receipt card
            const receipt = document.querySelector(".card.mx-auto").cloneNode(true);
            
            // Create a print-friendly window
            const printWindow = window.open("", "_blank");
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Booking Receipt</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            body { margin: 0; padding: 20px; }
                            .btn { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
            `);
            printWindow.document.write(receipt.outerHTML);
            printWindow.document.write(`
                    </div>
                    <div class="text-center mt-3">
                        <button onclick="window.print()" class="btn btn-primary me-2">Print</button>
                        <button onclick="window.close()" class="btn btn-secondary">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
        </script>';
    } else {
        throw new Exception("No rows affected - booking not saved");
    }
    
    $stmt->close();
} catch (Exception $e) {
    die(show_error("Booking failed: " . $e->getMessage()));
}
?>