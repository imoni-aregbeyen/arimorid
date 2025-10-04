<?php
// dashboard/pg/payment-callback.php
// Handles Paystack payment callback for additional services (addon orders)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}

// Get Paystack reference from query string
$reference = $_GET['reference'] ?? '';
if (!$reference) {
    die(show_error('No payment reference provided.'));
}

// Verify payment with Paystack
$paystack_secret_key = SK_TEST;
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . urlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
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
    die(show_error('Payment verification failed: ' . $err));
}

$result = json_decode($response);
if (!$result->status || $result->data->status !== 'success') {
    die(show_error('Payment not successful. Please try again.'));
}

// Payment successful, process order
$metadata = $result->data->metadata ?? [];
if (is_object($metadata) && !empty($metadata->batch_id)) {
  // Mark addon_orders as paid (settled)
  $batch_id = $metadata->batch_id;
  $stmt = $conn->prepare("UPDATE addon_orders SET status = 1 WHERE batch_id = ?");
  $stmt->bind_param("s", $batch_id);
  $stmt->execute();
  $stmt->close();
}

// Optionally clear session
unset($_SESSION['pending_addon_order']);

// Show success message
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="alert alert-success">
        <h4 class="mb-3">Payment Successful!</h4>
        <p>Your additional service order has been processed and marked as settled.</p>
        <a href="./?page=user-addons" class="btn btn-primary">View My Orders</a>
      </div>
    </div>
  </div>
</div>
<?php
