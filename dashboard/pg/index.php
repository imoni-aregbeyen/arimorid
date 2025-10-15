<?php
// session_start();
// print_r($_SESSION);
// Array ( [user_id] => 1 
// [user_role] => admin 
// [user_email] => admin@arimoridgr.com.ng 
// [user_name] => Administrator 
// [logged_in] => 1 )
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Get selected month/year from filter or use current
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Common data for all roles
$sql = "SELECT * FROM users WHERE role != 'admin'";
$rs = $conn->query($sql);
$users_rows = $rs->num_rows ?? 0;

// Role-specific data
if ($user_role === 'admin') {
    // Admin queries with explicit table names
    $apartments_count = $conn->query("SELECT COUNT(*) FROM service_apartments WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $bookings_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $total_revenue = $conn->query("SELECT SUM(total_cost) FROM bookings WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $caution_fee = $conn->query("SELECT SUM(caution_fee) FROM bookings WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $vat_fee = $conn->query("SELECT SUM(vat) FROM bookings WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $owner_fee = $conn->query("SELECT SUM(sa.owner_daily_charge * b.days) FROM bookings b JOIN service_apartments sa ON b.apartment_id = sa.id WHERE YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $listing_daily_charge = $conn->query("SELECT SUM(total_daily_charge) FROM bookings WHERE YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
    $daily_charge_margin = $listing_daily_charge - $owner_fee;
    
} elseif ($user_role === 'owner') {
    // Get owner's apartment count
    $apartments_sql = "SELECT COUNT(*) as count FROM service_apartments WHERE owner_id = $user_id AND YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month";
    $apartments_result = $conn->query($apartments_sql);
    $apartments_count = $apartments_result->fetch_assoc()['count'] ?? 0;
    
    // Get owner's booking count
    $bookings_sql = "SELECT COUNT(b.id) as count 
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     WHERE a.owner_id = $user_id AND YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month";
    $bookings_result = $conn->query($bookings_sql);
    $bookings_count = $bookings_result->fetch_assoc()['count'] ?? 0;
    
    // Get owner's total earnings
    $earnings_sql = "SELECT SUM(b.owner_total) as earnings 
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     WHERE a.owner_id = $user_id AND YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month";
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
<!-- Filter UI -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Filter Statistics</h6>
                <form method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="page" value="index">
                    <label for="month" class="me-2"><strong>Month:</strong></label>
                    <select name="month" id="month" class="form-select me-3" style="width:auto;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <label for="year" class="me-2"><strong>Year:</strong></label>
                    <select name="year" id="year" class="form-select me-3" style="width:auto;">
                        <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                    <a href="./" class="btn btn-outline-secondary">Reset</a>
                </form>
                <div class="mt-2">
                    <small class="text-muted">Showing statistics for: <strong><?= date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></strong></small>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] start -->
<div class="row">
    <?php if ($user_role === 'admin'): ?>
        <!-- Admin Dashboard -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Total Users</h6>
                    <h4 class="mb-3"><?= number_format($users_rows) ?></h4>
                    <small class="text-muted">All registered users</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">New Apartments</h6>
                    <h4 class="mb-3"><?= number_format($apartments_count) ?></h4>
                    <small class="text-muted">Added in <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Bookings</h6>
                    <h4 class="mb-3"><?= number_format($bookings_count) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Revenue</h6>
                    <h4 class="mb-3">₦<?= number_format($total_revenue, 2) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <!-- New Stat Cards -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Caution Fee</h6>
                    <h4 class="mb-3">₦<?= number_format($caution_fee, 2) ?></h4>
                    <small class="text-muted">Sum for <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">VAT Fee</h6>
                    <h4 class="mb-3">₦<?= number_format($vat_fee, 2) ?></h4>
                    <small class="text-muted">Sum for <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Owner Fee</h6>
                    <h4 class="mb-3">₦<?= number_format($owner_fee, 2) ?></h4>
                    <small class="text-muted">Sum for <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Daily Charge Margin</h6>
                    <h4 class="mb-3">₦<?= number_format($daily_charge_margin, 2) ?></h4>
                    <small class="text-muted">(Listing Daily Charge - Owner Fee) for <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>

        <!-- Recent Bookings for Admin -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Bookings</h5>
                </div>
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
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_bookings = $conn->query("
                     SELECT b.id, b.days, b.total_cost, b.created_at, b.check_in, b.check_out,
                         a.title as apartment_title, u.name as customer_name
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     JOIN users u ON b.user_id = u.id
                     WHERE YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month
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
                                            <td>" . date('M j, Y', strtotime($booking['check_in'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['check_out'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No bookings found for " . date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) . "</td></tr>";
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
                    <small class="text-muted">Added in <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Bookings</h6>
                    <h4 class="mb-3"><?= number_format($bookings_count) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Earnings</h6>
                    <h4 class="mb-3">₦<?= number_format($total_earnings, 2) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
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
            <div class="card">
                <div class="card-header">
                    <h5>Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Apartment</th>
                                    <th>Customer</th>
                                    <th>Days</th>
                                    <th>Your Earnings</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $owner_bookings = $conn->query("
                     SELECT b.id, b.days, b.total_cost, b.owner_total, b.created_at, b.check_in, b.check_out,
                         a.title as apartment_title, u.name as customer_name
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     JOIN users u ON b.user_id = u.id
                     WHERE a.owner_id = $user_id AND YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month
                     ORDER BY b.created_at DESC LIMIT 5
                                ");
                                
                                if ($owner_bookings->num_rows > 0) {
                                    while($booking = $owner_bookings->fetch_assoc()) {
                                        $owner_earnings = $booking['owner_total'];
                                        echo "<tr>
                                            <td>#{$booking['id']}</td>
                                            <td>{$booking['apartment_title']}</td>
                                            <td>{$booking['customer_name']}</td>
                                            <td>{$booking['days']}</td>
                                            <td>₦" . number_format($owner_earnings, 2) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['check_in'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['check_out'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No bookings found for " . date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) . "</td></tr>";
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
        <?php
        // User-specific filtered data
        $user_bookings_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE user_id = $user_id AND YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
        $user_total_spent = $conn->query("SELECT SUM(total_cost) FROM bookings WHERE user_id = $user_id AND YEAR(created_at) = $selected_year AND MONTH(created_at) = $selected_month")->fetch_row()[0] ?? 0;
        ?>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Bookings</h6>
                    <h4 class="mb-3"><?= number_format($user_bookings_count) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">Monthly Spent</h6>
                    <h4 class="mb-3">₦<?= number_format($user_total_spent, 2) ?></h4>
                    <small class="text-muted">For <?= date('M Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></small>
                </div>
            </div>
        </div>

        <!-- Recent Bookings for User -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>My Recent Bookings</h5>
                </div>
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
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $user_bookings = $conn->query("
                     SELECT b.id, b.days, b.total_cost, b.created_at, b.check_in, b.check_out,
                         a.title as apartment_title, b.status
                     FROM bookings b
                     JOIN service_apartments a ON b.apartment_id = a.id
                     WHERE b.user_id = $user_id AND YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month
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
                                            <td>" . date('M j, Y', strtotime($booking['check_in'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['check_out'])) . "</td>
                                            <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No bookings found for " . date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) . "</td></tr>";
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