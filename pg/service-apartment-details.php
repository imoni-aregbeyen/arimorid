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
    $addons = get_data('addons');
    
    // Calculate costs
    $days = max(1, (int)($_POST['days'] ?? 1));
    $listing_daily_charge = (float)$apartment['listing_daily_charge'];
    $service_charge = (float)$apartment['service_charge'];
    $total_daily_charge = $listing_daily_charge * $days;
    $addons_cost = 0;
    $selected_addons = [];
    
    foreach ($addons as $addon) {
        if (in_array($addon['id'], $_POST['addons'] ?? [])) {
            $addons_cost += (float)$addon['price'];
            $selected_addons[] = $addon['id'];
        }
    }
    
    $vat = $total_daily_charge * 0.075;
    $total_cost = $total_daily_charge + $service_charge + $addons_cost + $vat;

    // Store booking details in session
    $_SESSION['pending_booking'] = [
        'apartment_id' => $apartment['id'],
        'days' => $days,
        'addons' => $selected_addons,
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
$addons = get_data('addons');

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
                <div class="mb-3">
                    <label for="days" class="form-label"><strong>Number of Days:</strong></label>
                    <input type="number" id="days" name="days" class="form-control" value="1" min="1">
                </div>
                <?php if (!empty($addons)): ?>
                    <div class="mb-3">
                        <h4>Available Services</h4>
                        <?php foreach ($addons as $addon): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="addons[]" value="<?php echo $addon['id']; ?>" id="addon-<?php echo $addon['id']; ?>" data-price="<?php echo $addon['price']; ?>">
                                <label class="form-check-label" for="addon-<?php echo $addon['id']; ?>">
                                    <?php echo htmlspecialchars($addon['service']); ?> (₦<?php echo number_format($addon['price'], 2); ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between">
                    <p><strong>Total Daily Charge:</strong></p>
                    <p id="total-daily-charge">₦<?php echo number_format($apartment['listing_daily_charge'], 2); ?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>Caution Fee:</strong></p>
                    <p>₦<?php echo number_format($apartment['service_charge'], 2); ?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>Services:</strong></p>
                    <p id="addons-cost">₦0.00</p>
                </div>
                <div class="small text-muted" id="addons-list"></div>
                <hr>
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
    const addons = document.querySelectorAll('.form-check-input');
    const addonsCostElement = document.querySelector('#addons-cost');
    const addonsListElement = document.querySelector('#addons-list');
    const totalDailyChargeElement = document.querySelector('#total-daily-charge');
    const vatElement = document.querySelector('#vat');
    const totalCostElement = document.querySelector('#total-cost');
    const daysInput = document.querySelector('#days');

    const dailyListingCharge = <?php echo (float)$apartment['listing_daily_charge']; ?>;
    const serviceCharge = <?php echo (float)$apartment['service_charge']; ?>;
    const vatRate = 0.075;

    function updateTotalCost() {
        const days = parseInt(daysInput.value) || 1;
        let addonsCost = 0;
        let selectedAddons = [];

        addons.forEach(addon => {
            if (addon.checked) {
                addonsCost += parseFloat(addon.dataset.price);
                selectedAddons.push(addon.nextElementSibling.textContent.trim());
            }
        });

        const totalDailyCharge = dailyListingCharge * days;
        const vat = totalDailyCharge * vatRate;
        const totalCost = totalDailyCharge + serviceCharge + addonsCost + vat;

        totalDailyChargeElement.textContent = `₦${totalDailyCharge.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        addonsCostElement.textContent = `₦${addonsCost.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        addonsListElement.textContent = selectedAddons.length > 0 ? selectedAddons.join(', ') : 'None';
        vatElement.textContent = `₦${vat.toLocaleString(undefined, {minimumFractionDigits:2})}`;
        totalCostElement.textContent = `₦${totalCost.toLocaleString(undefined, {minimumFractionDigits:2})}`;
    }

    addons.forEach(addon => {
        addon.addEventListener('change', updateTotalCost);
    });

    daysInput.addEventListener('input', updateTotalCost);
    updateTotalCost(); // Initial calculation
});
</script>