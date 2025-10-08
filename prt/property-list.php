<style>
.property-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    object-position: center;
    display: block;
}
</style>
<div class="container-xxl py-5" id="property-list">
            <div class="container">
                <div class="row g-0 gx-5 align-items-end">
                    <div class="col-lg-6">
                        <div class="text-start mx-auto mb-5 wow slideInLeft" data-wow-delay="0.1s">
                            <h1 class="mb-3">Property Listing</h1>
                            <p>Explore our wide range of properties tailored to meet your needs. Whether you're looking to buy, rent, or invest, we have the perfect options for you.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 text-start text-lg-end wow slideInRight" data-wow-delay="0.1s">
                        <ul class="nav nav-pills d-inline-flex justify-content-end mb-5">
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-primary active" data-bs-toggle="pill" href="#tab-1">Service Apartments</a>
                            </li>
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-primary" data-bs-toggle="pill" href="#tab-2">For Sell</a>
                            </li>
                            <li class="nav-item me-0">
                                <a class="btn btn-outline-primary" data-bs-toggle="pill" href="#tab-3">For Rent</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane fade show p-0 active">
                        <div class="row g-4">
                            <?php $delay = 0.1; foreach ($service_apartments as $property): $images = explode(',', $property['images']); ?>
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $delay ?>s">
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href=""><img class="img-fluid property-img" src="./uploads/<?= $images[0] ?>" alt="<?= $images[0] ?>"></a>
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">&#8358;<?= number_format($property['listing_daily_charge']) ?></h5>
                                        <a class="d-block h5 mb-2" href=""><?= $property['title'] ?></a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i><?= $property['address'] ?></p>
                                    </div>
                                    <div class="d-flex border-top">
                                        
                                    </div>
                                    <div class="text-center p-3">
                                        <?php if (!isset($property['status']) || $property['status'] == 1): ?>
                                            <a class="btn btn-outline-primary w-100 py-2 px-4" href="?page=service-apartment-details&id=<?= $property['id'] ?>">Book Now</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100 py-2 px-4" disabled>Suspended</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php $delay = ($delay < 0.5) ? ($delay + 0.2) : 0.1; endforeach; ?>
                            <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.1s">
                                <a class="btn btn-primary py-3 px-5" href="?page=property-list&view=<?= ($view + 1) ?>">Browse More Apartments</a>
                            </div>
                        </div>
                    </div>
                    <div id="tab-2" class="tab-pane fade show p-0">
                        <div class="row g-4">
                            <?php $delay = 0.1; foreach ($properties as $property): if($property['for_sell_rent'] !== 'Sell'){continue;} $images = json_decode($property['images']); ?>
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $delay ?>s">
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href=""><img class="img-fluid property-img" src="./uploads/properties/<?= $images[0] ?>" alt=""></a>
                                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">For <?= $property['for_sell_rent'] ?></div>
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">&#8358;<?= number_format($property['listing_price']) ?></h5>
                                        <a class="d-block h5 mb-2" href=""><?= $property['title'] ?></a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i><?= $property['address'] ?></p>
                                        <p><i class="fa fa-ruler-combined text-success me-2"></i><?= $property['sqft'] ? $property['sqft'] . ' sqft' : 'N/A' ?></p>
                                        <p><i class="fa fa-info-circle text-info me-2"></i><?= $property['property_type'] ?></p>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a class="btn btn-success py-2 px-4 mb-1" href="?page=other-apartment-details&id=<?= $property['id'] ?>">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <?php $delay = ($delay < 0.5) ? ($delay + 0.2) : 0.1; endforeach; ?>
                            <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.1s">
                                <a class="btn btn-primary py-3 px-5" href="?page=property-list&view=<?= ($view + 1) ?>">Browse More Property</a>
                            </div>
                        </div>
                    </div>
                    <div id="tab-3" class="tab-pane fade show p-0">
                        <div class="row g-4">
                            <?php $delay = 0.1; foreach ($properties as $property): if($property['for_sell_rent'] !== 'Rent'){continue;} $images = json_decode($property['images']); ?>
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= $delay ?>s">
                                <div class="property-item rounded overflow-hidden">
                                    <div class="position-relative overflow-hidden">
                                        <a href=""><img class="img-fluid property-img" src="./uploads/properties/<?= $images[0] ?>" alt=""></a>
                                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">For <?= $property['for_sell_rent'] ?></div>
                                    </div>
                                    <div class="p-4 pb-0">
                                        <h5 class="text-primary mb-3">&#8358;<?= number_format($property['listing_price']) ?></h5>
                                        <a class="d-block h5 mb-2" href=""><?= $property['title'] ?></a>
                                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i><?= $property['address'] ?></p>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a class="btn btn-success py-2 px-4 mb-1" href="?page=other-apartment-details&id=<?= $property['id'] ?>">View Details</a>
                                    </div>
                                </div>
                            </div>
                            <?php $delay = ($delay < 0.5) ? ($delay + 0.2) : 0.1; endforeach; ?>
                            <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.1s">
                                <a class="btn btn-primary py-3 px-5" href="?page=property-list&view=<?= ($view + 1) ?>">Browse More Property</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>