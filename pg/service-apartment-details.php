<?php
// service-apartment-details.php

// If this is a POST request, handle it and exit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    handleBookingRequest();
    // handleBookingRequest() will call header() and exit if needed
    // If we get here, it means we should show the form
}

function handleBookingRequest() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./?page=login");
        exit;
    }

    // Check verification status
    if (!isset($_SESSION['user_verified']) || $_SESSION['user_verified'] != 1) {
        $_SESSION['booking_error'] = 'You must complete your KYC before booking. <a href="./dashboard/pg/kyc.php" class="alert-link">Update your KYC information here</a>.';
        header("Location: ./?page=service-apartment-details&id=" . ($_GET['id'] ?? ''));
        exit;
    }

    $id = (int)($_GET['id'] ?? NULL);
    if (!$id) {
        return;
    }
    $apartment = get_data("service_apartments", "WHERE id=$id")[0] ?? null;
    if (!$apartment) {
        return;
    }
    $units = (int)($apartment['units'] ?? 1);
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    if ($check_in && $check_out) {
        $sql = "SELECT COUNT(*) as count FROM bookings WHERE apartment_id = ? AND (
            (check_in <= ? AND check_out > ?) OR
            (check_in < ? AND check_out >= ?) OR
            (check_in >= ? AND check_out <= ?)
        )"; $conn = $GLOBALS['conn'];
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $clash_count = (int)$row['count'];
        if ($clash_count >= $units) {
            $_SESSION['booking_error'] = "Selected dates are fully booked. Please choose different dates.";
            header("Location: ./?page=service-apartment-details&id=$id");
            exit;
        }
    }
    // Calculate costs
    $days = max(1, (int)($_POST['days'] ?? 1));
    $listing_daily_charge = (float)$apartment['listing_daily_charge'];
    $service_charge = (float)$apartment['service_charge'];
    $total_daily_charge = $listing_daily_charge * $days;
    $vat = $total_daily_charge * 0.075;
    $total_cost = $total_daily_charge + $service_charge + $vat;
    // Store booking details in session
    $_SESSION['pending_booking'] = [
        'apartment_id' => $apartment['id'],
        'days' => $days,
        'total_cost' => $total_cost,
        'user_id' => $_SESSION['user_id'],
        'user_email' => $_SESSION['user_email'] ?? 'customer@example.com',
        'check_in' => $check_in,
        'check_out' => $check_out
    ];
    header("Location: ./?page=process-payment");
    exit;
}

// Normal page display logic continues below...
$id = (int)($_GET['id'] ?? NULL);
if (!$id) {
    echo show_error("Invalid apartment ID");
    return;
}

$apartment = get_data("service_apartments", "WHERE id=$id")[0] ?? null;
if (!$apartment) {
    echo show_error("Apartment not found");
    return;
}

$apartment['images'] = is_array($apartment['images']) ? $apartment['images'] : explode(',', $apartment['images']);

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}
?>

<!-- Apartment Details UI -->
<div class="container py-5">
    <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['booking_error']; ?></div>
        <?php unset($_SESSION['booking_error']); ?>
    <?php endif; ?>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div id="apartmentCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($apartment['images'] as $index => $image): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="./uploads/<?php echo htmlspecialchars($image); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($apartment['title']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <div class="col-lg-4">
            <h2 class="mb-3"><?php echo htmlspecialchars($apartment['title']); ?></h2>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($apartment['address']); ?></p>
            <p><strong>Listing Daily Charge:</strong> ₦<?php echo number_format($apartment['listing_daily_charge'], 2); ?></p>
            <p><strong>Caution Fee:</strong> ₦<?php echo number_format($apartment['service_charge'], 2); ?></p>
            <form method="POST" action="" id="booking-form">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="row mb-3">
                    <div class="col-lg">
                        <label for="checkIn" class="form-label">Check In</label>
                        <input type="date" name="check_in" id="checkIn" class="form-control" required>
                    </div>
                    <div class="col-lg">
                        <label for="checkOut" class="form-label">Check Out</label>
                        <input type="date" name="check_out" id="checkOut" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="days" class="form-label"><strong>Number of Days:</strong></label>
                    <input type="number" id="days" name="days" class="form-control" value="1" min="1" readonly>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>Total Daily Charge:</strong></p>
                    <p id="total-daily-charge">₦<?php echo number_format($apartment['listing_daily_charge'], 2); ?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>Caution Fee:</strong></p>
                    <p>₦<?php echo number_format($apartment['service_charge'], 2); ?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>VAT (7.5%):</strong></p>
                    <p id="vat">₦<?php echo number_format($apartment['listing_daily_charge'] * 0.075, 2); ?></p>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <p><strong>Total Cost:</strong></p>
                    <p id="total-cost" class="fw-bold">₦<?php echo number_format($apartment['listing_daily_charge'] + $apartment['service_charge'] + ($apartment['listing_daily_charge'] * 0.075), 2); ?></p>
                </div>
                <hr>
                <button type="submit" name="book_now" class="btn btn-primary w-100 mt-3">Proceed to Payment</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalDailyChargeElement = document.querySelector('#total-daily-charge');
    const vatElement = document.querySelector('#vat');
    const totalCostElement = document.querySelector('#total-cost');
    const daysInput = document.querySelector('#days');
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');

    const dailyListingCharge = <?php echo (float)$apartment['listing_daily_charge']; ?>;
    const serviceCharge = <?php echo (float)$apartment['service_charge']; ?>;
    const vatRate = 0.075;

    function calculateDays() {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        if (checkIn && checkOut) {
            const inDate = new Date(checkIn);
            const outDate = new Date(checkOut);
            const diffTime = outDate - inDate;
            const diffDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
            daysInput.value = diffDays;
        } else {
            daysInput.value = 1;
        }
    }

    function updateTotalCost() {
        calculateDays();
        const days = parseInt(daysInput.value) || 1;
        const totalDailyCharge = dailyListingCharge * days;
        const vat = totalDailyCharge * vatRate;
        const totalCost = totalDailyCharge + serviceCharge + vat;

        totalDailyChargeElement.textContent = `₦${totalDailyCharge.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        vatElement.textContent = `₦${vat.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        totalCostElement.textContent = `₦${totalCost.toLocaleString(undefined, {minimumFractionDigits:2})}`;
    }

    checkInInput.addEventListener('change', updateTotalCost);
    checkOutInput.addEventListener('change', updateTotalCost);
    daysInput.addEventListener('input', updateTotalCost);
    updateTotalCost(); // Initial calculation
});
</script>