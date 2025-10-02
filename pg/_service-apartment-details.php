<?php
// service-apartment-details.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle booking request - MUST BE AT THE TOP BEFORE ANY OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./?page=login");
        exit;
    }

    $id = (int)$_GET['id'] ?? NULL;
    $apartment = get_data("service_apartments", "WHERE id=$id")[0];
    
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
        'user_email' => $_SESSION['user_email'] ?? 'customer@example.com'
    ];

    // Redirect to payment processor
    header("Location: ./?page=process-payment");
    exit;
}

// Now get the apartment data for display
$id = (int)$_GET['id'] ?? NULL;
$apartment = get_data("service_apartments", "WHERE id=$id")[0];
$apartment['images'] = is_array($apartment['images']) ? $apartment['images'] : explode(',', $apartment['images']);

function show_error($message) {
    return '<div class="alert alert-danger">Error: ' . htmlspecialchars($message) . '</div>';
}
?>

<!-- Apartment Details UI -->
<div class="container py-5">
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