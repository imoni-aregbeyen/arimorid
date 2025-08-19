<?php
// pg/booking-details.php

// Check if user is logged in (already handled in index.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}

function show_success($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    die(show_error("Invalid booking ID"));
}

// Fetch booking details with related data
try {
    $query = "SELECT b.*, 
                     u.name as customer_name, 
                     u.email as customer_email,
                     u.phone as customer_phone,
                     a.title as apartment_title,
                     a.address as apartment_address,
                     a.images as apartment_images
              FROM bookings b
              LEFT JOIN users u ON b.user_id = u.id
              LEFT JOIN service_apartments a ON b.apartment_id = a.id
              WHERE b.id = ?";
    
    $stmt = $GLOBALS['conn']->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die(show_error("Booking not found"));
    }
    
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    // Parse apartment images
    $booking['apartment_images'] = !empty($booking['apartment_images']) ? 
        explode(',', $booking['apartment_images']) : [];
    
    // Parse addons if they exist
    $addons_list = !empty($booking['addons']) ? explode(', ', $booking['addons']) : [];
    
} catch (Exception $e) {
    die(show_error("Database error: " . $e->getMessage()));
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $GLOBALS['conn']->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking status updated successfully";
            header("Location: ?page=booking-details&id=$booking_id");
            exit;
        } else {
            echo show_error("Failed to update booking status");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo show_error("Database error: " . $e->getMessage());
    }
}

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo show_success($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Booking Details #<?= $booking['id'] ?></h5>
                <div>
                    <span class="badge bg-<?= 
                        $booking['status'] === 'confirmed' ? 'success' : 
                        ($booking['status'] === 'pending' ? 'warning' : 
                        ($booking['status'] === 'cancelled' ? 'danger' : 'info')) 
                    ?>">
                        <?= ucfirst($booking['status']) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6>Customer Information</h6>
                            <hr class="mt-1 mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p><?= htmlspecialchars($booking['customer_name']) ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p><?= htmlspecialchars($booking['customer_email']) ?></p>
                                </div>
                                <?php if (!empty($booking['customer_phone'])): ?>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Phone:</strong></p>
                                    <p><?= htmlspecialchars($booking['customer_phone']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Apartment Information</h6>
                            <hr class="mt-1 mb-3">
                            <p class="mb-1"><strong>Title:</strong></p>
                            <p><?= htmlspecialchars($booking['apartment_title']) ?></p>
                            
                            <p class="mb-1"><strong>Address:</strong></p>
                            <p><?= htmlspecialchars($booking['apartment_address']) ?></p>
                            
                            <?php if (!empty($booking['apartment_images'])): ?>
                            <p class="mb-1"><strong>Images:</strong></p>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($booking['apartment_images'] as $image): ?>
                                <a href="../uploads/<?= htmlspecialchars($image) ?>" target="_blank">
                                    <img src="../uploads/<?= htmlspecialchars($image) ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6>Booking Details</h6>
                            <hr class="mt-1 mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Booking Date:</strong></p>
                                    <p><?= date('M j, Y h:i A', strtotime($booking['created_at'])) ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Duration:</strong></p>
                                    <p><?= $booking['days'] ?> day<?= $booking['days'] > 1 ? 's' : '' ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Payment Reference:</strong></p>
                                    <p><?= htmlspecialchars($booking['payment_reference']) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Pricing Breakdown</h6>
                            <hr class="mt-1 mb-3">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Daily Charge (<?= $booking['days'] ?> days)</td>
                                            <td class="text-end">₦<?= number_format($booking['total_daily_charge'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Caution Fee</td>
                                            <td class="text-end">₦<?= number_format($booking['caution_fee'], 2) ?></td>
                                        </tr>
                                        <?php if (!empty($addons_list)): ?>
                                        <tr>
                                            <td>Additional Services (<?= count($addons_list) ?>)</td>
                                            <td class="text-end">₦<?= number_format($booking['addons_cost'], 2) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td>VAT (7.5%)</td>
                                            <td class="text-end">₦<?= number_format($booking['vat'], 2) ?></td>
                                        </tr>
                                        <tr class="table-active">
                                            <th>Total Cost</th>
                                            <th class="text-end">₦<?= number_format($booking['total_cost'], 2) ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($addons_list)): ?>
                        <div class="mb-4">
                            <h6>Additional Services</h6>
                            <hr class="mt-1 mb-3">
                            <ul class="list-group">
                                <?php foreach ($addons_list as $addon): ?>
                                <li class="list-group-item"><?= htmlspecialchars($addon) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="?page=bookings" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Back to Bookings
                            </a>
                            
                            <div class="d-flex gap-2">
                                
                                <a href="?page=bookings&delete=<?= $booking['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this booking?')">
                                    <i class="ti ti-trash"></i> Delete Booking
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // You can add any booking-specific JavaScript here
    // For example, image gallery functionality or status update notifications
});
</script>