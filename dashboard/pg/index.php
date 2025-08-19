<?php
// print_r($_SESSION);
// Array ( [user_id] => 1 
// [user_role] => admin 
// [user_verified] => 0 
// [user_email] => admin@arimoridgr.com.ng 
// [user_name] => Administrator 
// [logged_in] => 1 )
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Common data for all roles
$sql = "SELECT * FROM users WHERE role != 'admin'";
$rs = $conn->query($sql);
$users_rows = $rs->num_rows ?? 0;

// Role-specific data
if ($user_role === 'owner') {
    // Get owner's apartment count
    $apartments_sql = "SELECT COUNT(*) as count FROM service_apartments WHERE owner_id = $user_id";
    $apartments_result = $conn->query($apartments_sql);
    $apartments_count = $apartments_result->fetch_assoc()['count'] ?? 0;
    
    // Get owner's booking count
    $bookings_sql = "SELECT COUNT(b.id) as count 
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     WHERE a.owner_id = $user_id";
    $bookings_result = $conn->query($bookings_sql);
    $bookings_count = $bookings_result->fetch_assoc()['count'] ?? 0;
    
    // Get owner's total earnings
    $earnings_sql = "SELECT SUM(b.total_daily_charge * b.days) as earnings 
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     WHERE a.owner_id = $user_id";
    $earnings_result = $conn->query($earnings_sql);
    $total_earnings = $earnings_result->fetch_assoc()['earnings'] ?? 0;
}
?>      
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-10">Home</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard/index.html">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0)">Dashboard</a></li>
                    <li class="breadcrumb-item" aria-current="page">Home</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->
<!-- [ Main Content ] start -->
<div class="row">
    <?php if ($user_role === 'admin'): ?>
        <!-- Admin Dashboard -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Users</h6>
                    <h4 class="mb-3"><?= number_format($users_rows) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Apartments</h6>
                    <h4 class="mb-3"><?= number_format($conn->query("SELECT COUNT(*) FROM service_apartments")->fetch_row()[0]) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Bookings</h6>
                    <h4 class="mb-3"><?= number_format($conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0]) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Revenue</h6>
                    <h4 class="mb-3">₦<?= number_format($conn->query("SELECT SUM(total_cost) FROM bookings")->fetch_row()[0] ?? 0, 2) ?></h4>
                </div>
            </div>
        </div>

        <!-- Recent Bookings for Admin -->
        <div class="col-md-12">
            <h5 class="mb-3">Recent Bookings</h5>
            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Apartment</th>
                                    <th>Customer</th>
                                    <th>Days</th>
                                    <th>Total Cost</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_bookings = $conn->query("
                                    SELECT b.id, b.days, b.total_cost, b.created_at, 
                                           a.title as apartment_title, u.name as customer_name
                                    FROM bookings b
                                    JOIN service_apartments a ON b.apartment_id = a.id
                                    JOIN users u ON b.user_id = u.id
                                    ORDER BY b.created_at DESC LIMIT 5
                                ");
                                
                                if ($recent_bookings->num_rows > 0) {
                                    while($booking = $recent_bookings->fetch_assoc()) {
                                        echo "<tr>
                                            <td>#{$booking['id']}</td>
                                            <td>{$booking['apartment_title']}</td>
                                            <td>{$booking['customer_name']}</td>
                                            <td>{$booking['days']}</td>
                                            <td>₦" . number_format($booking['total_cost'], 2) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No recent bookings found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($user_role === 'owner'): ?>
        <!-- Owner Dashboard -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">My Apartments</h6>
                    <h4 class="mb-3"><?= number_format($apartments_count) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Bookings</h6>
                    <h4 class="mb-3"><?= number_format($bookings_count) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Earnings</h6>
                    <h4 class="mb-3">₦<?= number_format($total_earnings, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Available Balance</h6>
                    <h4 class="mb-3">₦<?= number_format($total_earnings * 0.8, 2) ?></h4>
                    <small>(After 20% service charge)</small>
                </div>
            </div>
        </div>

        <!-- Recent Bookings for Owner -->
        <div class="col-md-12">
            <h5 class="mb-3">Recent Bookings</h5>
            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Apartment</th>
                                    <th>Customer</th>
                                    <th>Days</th>
                                    <th>Total Cost</th>
                                    <th>Your Earnings</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $owner_bookings = $conn->query("
                                    SELECT b.id, b.days, b.total_cost, b.total_daily_charge, b.created_at, 
                                           a.title as apartment_title, u.name as customer_name
                                    FROM bookings b
                                    JOIN service_apartments a ON b.apartment_id = a.id
                                    JOIN users u ON b.user_id = u.id
                                    WHERE a.owner_id = $user_id
                                    ORDER BY b.created_at DESC LIMIT 5
                                ");
                                
                                if ($owner_bookings->num_rows > 0) {
                                    while($booking = $owner_bookings->fetch_assoc()) {
                                        $owner_earnings = $booking['total_daily_charge'] * $booking['days'];
                                        echo "<tr>
                                            <td>#{$booking['id']}</td>
                                            <td>{$booking['apartment_title']}</td>
                                            <td>{$booking['customer_name']}</td>
                                            <td>{$booking['days']}</td>
                                            <td>₦" . number_format($booking['total_cost'], 2) . "</td>
                                            <td>₦" . number_format($owner_earnings, 2) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No recent bookings found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- User Dashboard -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">My Bookings</h6>
                    <h4 class="mb-3"><?= number_format($conn->query("SELECT COUNT(*) FROM bookings WHERE user_id = $user_id")->fetch_row()[0]) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Spent</h6>
                    <h4 class="mb-3">₦<?= number_format($conn->query("SELECT SUM(total_cost) FROM bookings WHERE user_id = $user_id")->fetch_row()[0] ?? 0, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Upcoming Stays</h6>
                    <h4 class="mb-3">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Favorite Apartments</h6>
                    <h4 class="mb-3">0</h4>
                </div>
            </div>
        </div>

        <!-- Recent Bookings for User -->
        <div class="col-md-12">
            <h5 class="mb-3">My Recent Bookings</h5>
            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Apartment</th>
                                    <th>Days</th>
                                    <th>Total Cost</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $user_bookings = $conn->query("
                                    SELECT b.id, b.days, b.total_cost, b.created_at, 
                                           a.title as apartment_title, b.status
                                    FROM bookings b
                                    JOIN service_apartments a ON b.apartment_id = a.id
                                    WHERE b.user_id = $user_id
                                    ORDER BY b.created_at DESC LIMIT 5
                                ");
                                
                                if ($user_bookings->num_rows > 0) {
                                    while($booking = $user_bookings->fetch_assoc()) {
                                        $status_class = '';
                                        if ($booking['status'] === 'confirmed') $status_class = 'text-success';
                                        if ($booking['status'] === 'pending') $status_class = 'text-warning';
                                        if ($booking['status'] === 'cancelled') $status_class = 'text-danger';
                                        
                                        echo "<tr>
                                            <td>#{$booking['id']}</td>
                                            <td>{$booking['apartment_title']}</td>
                                            <td>{$booking['days']}</td>
                                            <td>₦" . number_format($booking['total_cost'], 2) . "</td>
                                            <td><span class='$status_class'>" . ucfirst($booking['status']) . "</span></td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No bookings found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>