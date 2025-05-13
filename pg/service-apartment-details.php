<?php
// addons: id, service, price, created_at, updated_at
// service_apartments: id, images(image1, image2), address, title, owner_daily_charge, listing_daily_charge, service_charge, created_at, updated_at

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $apartment_id = intval($_GET['id']);
    $sql = "SELECT * FROM service_apartments WHERE id = $apartment_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $apartment = mysqli_fetch_assoc($result);
        $apartment['images'] = explode(',', $apartment['images']); // Split images into an array

        // Fetch addons
        $addons_sql = "SELECT * FROM addons";
        $addons_result = mysqli_query($conn, $addons_sql);
        $addons = [];
        if ($addons_result && mysqli_num_rows($addons_result) > 0) {
            while ($addon = mysqli_fetch_assoc($addons_result)) {
                $addons[] = $addon;
            }
        }
    } else {
        echo "<p class='text-center'>Apartment not found.</p>";
        exit;
    }
} else {
    echo "<p class='text-center'>Invalid apartment ID.</p>";
    exit;
}
?>

<style>
    .carousel-inner img {
        height: 500px; /* Set a fixed height */
        object-fit: cover; /* Ensure the image covers the area without distortion */
    }
</style>

<!-- Header Start -->
<div class="container-fluid header bg-white p-0">
    <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
        <div class="col-md-12 pt-5 mt-lg-5">
            <div class="my-5"></div>
            <h1 class="display-5 animated fadeIn mb-4 text-center"><?php echo htmlspecialchars($apartment['title']); ?></h1>
        </div>
    </div>
</div>
<!-- Header End -->

<!-- Apartment Details Section Start -->
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
            <p><strong>Listing Daily Charge:</strong> ₦<?php echo number_format(htmlspecialchars($apartment['listing_daily_charge']), 2); ?></p>
            <p><strong>Caution Fee:</strong> ₦<?php echo number_format(htmlspecialchars($apartment['service_charge']), 2); ?></p>
            
            <!-- Available Services Section -->
            <?php if (!empty($addons)): ?>
                <div class="mt-4">
                    <h4>Available Services</h4>
                    <form method="GET" action="./?=booking">
                        <?php foreach ($addons as $addon): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="addons[]" value="<?php echo $addon['id']; ?>" id="addon-<?php echo $addon['id']; ?>" data-price="<?php echo $addon['price']; ?>">
                                <label class="form-check-label" for="addon-<?php echo $addon['id']; ?>">
                                    <?php echo htmlspecialchars($addon['service']); ?> (₦<?php echo number_format($addon['price'], 2); ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Total and VAT Section -->
            <div class="mt-4">
                <h4>Total Cost</h4>
                <div class="card p-3">
                    <div class="mb-3">
                        <label for="days" class="form-label"><strong>Number of Days:</strong></label>
                        <input type="number" id="days" class="form-control" value="1" min="1">
                    </div>
                    <?php
                    $base_cost = $apartment['listing_daily_charge'] + $apartment['service_charge'];
                    $vat = $base_cost * 0.075;
                    $total_cost = $base_cost + $vat;
                    ?>
                    <div class="d-flex justify-content-between">
                        <p><strong>Listing Daily Charge:</strong></p>
                        <p>₦<?php echo number_format($apartment['listing_daily_charge'], 2); ?></p>
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
                        <p><strong>Base Cost:</strong></p>
                        <p id="base-cost">₦<?php echo number_format($base_cost, 2); ?></p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p><strong>VAT (7.5%):</strong></p>
                        <p id="vat">₦<?php echo number_format($vat, 2); ?></p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <p><strong>Total Cost:</strong></p>
                        <p id="total-cost" class="fw-bold">₦<?php echo number_format($total_cost, 2); ?></p>
                    </div>
                    <hr>
                    <!-- Book Now Button -->
                    <?php if (!empty($addons)): ?>
                        <form method="GET" action="./?=booking">
                            <?php foreach ($addons as $addon): ?>
                                <input type="hidden" name="addons[]" value="<?php echo $addon['id']; ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="id" value="<?php echo $apartment_id; ?>">
                            <button type="submit" class="btn btn-primary w-100">Book Now</button>
                        </form>
                    <?php else: ?>
                        <a href="./?=booking&id=<?php echo $apartment_id; ?>" class="btn btn-primary w-100">Book Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Apartment Details Section End -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addons = document.querySelectorAll('.form-check-input');
        const addonsCostElement = document.querySelector('#addons-cost');
        const addonsListElement = document.querySelector('#addons-list');
        const baseCostElement = document.querySelector('#base-cost');
        const vatElement = document.querySelector('#vat');
        const totalCostElement = document.querySelector('#total-cost');
        const daysInput = document.querySelector('#days');

        const dailyListingCharge = parseFloat(<?php echo $apartment['listing_daily_charge']; ?>);
        const serviceCharge = parseFloat(<?php echo $apartment['service_charge']; ?>);
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

            const newBaseCost = (dailyListingCharge * days) + serviceCharge + addonsCost;
            const newVat = newBaseCost * vatRate;
            const newTotalCost = newBaseCost + newVat;

            addonsCostElement.textContent = `₦${addonsCost.toFixed(2)}`;
            addonsListElement.textContent = selectedAddons.length > 0 ? selectedAddons.join(', ') : 'None';
            baseCostElement.textContent = `₦${newBaseCost.toFixed(2)}`;
            vatElement.textContent = `₦${newVat.toFixed(2)}`;
            totalCostElement.textContent = `₦${newTotalCost.toFixed(2)}`;
        }

        addons.forEach(addon => {
            addon.addEventListener('change', updateTotalCost);
        });

        daysInput.addEventListener('input', updateTotalCost);
    });
</script>
