<?php
// pg/bookings.php

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

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $GLOBALS['conn']->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking status updated successfully";
        } else {
            $_SESSION['error_message'] = "Failed to update booking status";
        }
        
        $stmt->close();
        header("Location: ?page=bookings");
        exit;
    } catch (Exception $e) {
        echo show_error("Database error: " . $e->getMessage());
    }
}

// Handle booking deletion
if (isset($_GET['delete'])) {
    $booking_id = (int)$_GET['delete'];
    
    try {
        $stmt = $GLOBALS['conn']->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking deleted successfully";
        } else {
            $_SESSION['error_message'] = "Failed to delete booking";
        }
        
        $stmt->close();
        header("Location: ?page=bookings");
        exit;
    } catch (Exception $e) {
        echo show_error("Database error: " . $e->getMessage());
    }
}

// Fetch bookings based on user role
try {
    if ($_SESSION['user_role'] === 'owner') {
        // For owners, only show bookings for their apartments
        $query = "SELECT b.*, 
                         u.name as customer_name, 
                         u.email as customer_email,
                         a.title as apartment_title
                  FROM bookings b
                  LEFT JOIN users u ON b.user_id = u.id
                  LEFT JOIN service_apartments a ON b.apartment_id = a.id
                  WHERE a.owner_id = ?
                  ORDER BY b.created_at DESC";
        
        $stmt = $GLOBALS['conn']->prepare($query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        // For admin or other roles, show all bookings
        $query = "SELECT b.*, 
                         u.name as customer_name, 
                         u.email as customer_email,
                         a.title as apartment_title
                  FROM bookings b
                  LEFT JOIN users u ON b.user_id = u.id
                  LEFT JOIN service_apartments a ON b.apartment_id = a.id
                  ORDER BY b.created_at DESC";
        
        $result = $GLOBALS['conn']->query($query);
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    if (!$result) {
        throw new Exception("Failed to fetch bookings: " . $GLOBALS['conn']->error);
    }
} catch (Exception $e) {
    echo show_error("Database error: " . $e->getMessage());
    $bookings = [];
}

// Display success/error messages
if (isset($_SESSION['success_message'])) {
    echo show_success($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo show_error($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>All Bookings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Apartment</th>
                                <th>Days</th>
                                <th>Total Cost</th>
                                <th>Payment Ref</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No bookings found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['id']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($booking['customer_name']) ?><br>
                                        <small><?= htmlspecialchars($booking['customer_email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['apartment_title']) ?></td>
                                    <td><?= htmlspecialchars($booking['days']) ?></td>
                                    <td>₦<?= number_format($booking['total_cost'], 2) ?></td>
                                    <td><?= htmlspecialchars($booking['payment_reference']) ?></td>
                                    <td><?= date('M j, Y', strtotime($booking['created_at'])) ?></td>
                                    <td>
                                        <a href="?page=booking-details&id=<?= $booking['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                            <a href="?page=bookings&delete=<?= $booking['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // You can add interactive functionality here if needed
});
</script>