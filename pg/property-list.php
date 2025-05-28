<?php
// properties: id, property_type, for_sell_rent, owner_price, listing_price, service_charge, title, address, sqft, bed, bath images[], owner_id, created_at
$sql = "SELECT * FROM properties ORDER BY created_at DESC";
$result = $conn->query($sql);
$properties = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$apartments = $properties;
// print_r($properties); // Uncomment for debugging if needed
?>

<!-- Header Start -->
<div class="container-fluid header bg-white p-0">
    <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
        <div class="col-md-12 pt-5 mt-lg-5">
            <div class="my-5"></div>
            <h1 class="display-5 animated fadeIn mb-4 text-center">Properties for Sell / Rent</h1>
        </div>
    </div>
</div>
<!-- Header End -->

<!-- Apartments Section Start -->
<div class="container py-5">
    <div class="row g-4">
        <?php if (!empty($apartments)): ?>
            <?php foreach ($apartments as $apartment): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <img src="./uploads/<?php echo htmlspecialchars($apartment['images'][0]); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($apartment['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($apartment['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($apartment['address']); ?></p>
                            <p class="card-text">Daily Charge: ₦<?php echo number_format(htmlspecialchars($apartment['listing_price']), 2); ?></p>
                            <a href="./?page=service-apartment-details&id=<?= $apartment['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No apartments available at the moment.</p>
        <?php endif; ?>
    </div>
</div>
<!-- Apartments Section End -->