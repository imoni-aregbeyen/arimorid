<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT * FROM properties WHERE id = $id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $apartment = $result->fetch_assoc();
    $images = json_decode($apartment['images']);
} else {
    echo '<div class="alert alert-danger">Apartment not found.</div>';
    exit;
}
?>
<style>
    .carousel-inner img {
        max-height: 350px;
        object-fit: cover;
        width: 100%;
    }
</style>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div id="apartmentCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="./uploads/properties/<?php echo htmlspecialchars($image); ?>" class="d-block w-100 card" alt="<?php echo htmlspecialchars($apartment['title']); ?>">
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
            <p><strong>Details:</strong> <?php echo htmlspecialchars($apartment['address']); ?></p>
            <!-- <p><strong>Type:</strong> <?php echo htmlspecialchars($apartment['property_type']); ?></p> -->
            <p><strong>For:</strong> <?php echo htmlspecialchars($apartment['for_sell_rent']); ?></p>
            
            <p><strong>Listing Price:</strong> ₦<?php echo number_format($apartment['listing_price'], 2); ?></p>
            <p><strong>Date Listed:</strong> <?php echo date('M j, Y', strtotime($apartment['created_at'])); ?></p>
            <p class=""><a href="?page=contact" class="btn btn-primary px-4">Contact Us</a></p>
        </div>
    </div>
</div>
