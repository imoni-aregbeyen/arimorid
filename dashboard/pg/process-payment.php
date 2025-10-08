<?php
// dashboard/pg/process-payment.php
// Handles payment for additional services (addon orders)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

// Get pending addon order from session
$addon_order = $_SESSION['pending_addon_order'] ?? null;
// Array ( [batch_id] => batch_12_68e3bf4f72436 [user_id] => 12 [orders] => Array ( [0] => Array ( [addon_id] => 6 [service] => Sample Service [days] => 1 [price] => 1000 [subtotal] => 1000 ) [1] => Array ( [addon_id] => 4 [service] => Breakfast [days] => 2 [price] => 5000 [subtotal] => 10000 ) ) [total] => 11000 [vat] => 825 [grand_total] => 11825 [user_email] => peterparker@example.com )
if (!$addon_order || empty($addon_order['orders'])) {
    echo '<div class="alert alert-danger">No pending addon order found.</div>';
    return;
}

// Payment logic (dummy UI for now)
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <?php
    // dashboard/pg/process-payment.php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    function show_error($message) {
      return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
    }

    // Check if we have addon order data
    if (!isset($_SESSION['pending_addon_order'])) {
      die(show_error("No addon order data found. Please start the order process again."));
    }

    $addon_order = $_SESSION['pending_addon_order'];
    $paystack_secret_key = SK_TEST;

    // Initialize Paystack payment
    $callback_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . 
             $_SERVER['HTTP_HOST'] . '/dashboard/?page=payment-callback';
    $callback_url = CALLBACK_URL;
    // http://localhost/arimorid/?page=payment-callback
  // Add 'dashboard/' before ?page in callback_url if not present
  if (strpos($callback_url, '/dashboard/') === false) {
    $callback_url_2 = preg_replace('/(\/)(\?page=)/', '$1dashboard/$2', $callback_url);
  } else {
    $callback_url_2 = $callback_url;
  }

    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode([
        'amount' => $addon_order['grand_total'] * 100,
        'email' => $addon_order['user_email'],
        'callback_url' => $callback_url_2,
        'metadata' => $addon_order
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
