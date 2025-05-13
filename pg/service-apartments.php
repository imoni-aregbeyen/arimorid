<?php
// service_apartments: id, images(image1, image2), address, title, owner_daily_charge, listing_daily_charge, service_charge, created_at, updated_at
$sql = "SELECT * FROM service_apartments";
$result = mysqli_query($conn, $sql);
if ($result) {
    $apartments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    foreach ($apartments as &$apartment) {
        $apartment['images'] = explode(',', $apartment['images']); // Split images into an array
    }
    unset($apartment); // Break reference
} else {
    echo "Error: " . mysqli_error($conn);
}

?>

<!-- Header Start -->
<div class="container-fluid header bg-white p-0">
    <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
        <div class="col-md-12 pt-5 mt-lg-5">
            <div class="my-5"></div>
            <h1 class="display-5 animated fadeIn mb-4 text-center">Service Apartments</h1>
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
                            <p class="card-text">Daily Charge: ₦<?php echo number_format(htmlspecialchars($apartment['listing_daily_charge']), 2); ?></p>
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