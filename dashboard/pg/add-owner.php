<?php
$property_id = test_input($_GET['id']);
$property = $conn->query("SELECT * FROM properties WHERE id = '$property_id'")->fetch_assoc();
if (!$property) {
    echo "<script>alert('Property not found!'); window.location.href='?page=properties';</script>";
    exit;
}
$images = json_decode($property['images'], true);
if (!$images) {
    $images = [];
}
?>      
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Add Owner</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?page=properties">Properties</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header">
              <!-- <h5>Hello card</h5> -->
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg">
                  <h4><span class="border-bottom">Add Owner</span></h4>
                  <form action="./ac/action-add-new-owner.php" method="post" enctype="multipart/form-data">
                    <details>
                      <summary>New Owner</summary>
                      <label>Name</label>
                      <input type="text" name="name" class="form-control" required>
                      <label>Phone</label>
                      <input type="text" name="phone" class="form-control" required>
                      <label>Email</label>
                      <input type="email" name="email" class="form-control" required>
                      <label>Account Number</label>
                      <input type="text" name="account_number" class="form-control" required>
                      <label>Bank Name</label>
                      <input type="text" name="bank_name" class="form-control" required>
                      <label>Account Name</label>
                      <input type="text" name="account_name" class="form-control" required>
                      <input type="hidden" name="property_id" value="<?= $property['id'] ?>" readonly>
                      <input type="submit" class="btn btn-primary" value="Add Owner">
                    </details>
                  </form>
                  <hr>
                  <form action="./ac/action-update-owner.php" method="post" enctype="multipart/form-data">
                    <details>
                      <summary>Existing Owner</summary>
                    </details>
                  </form>
                </div>
                <div class="col-lg">
                  <h4><span class="border-bottom">Property Details</span></h4>
                  <p><strong>Property Type:</strong> <?= $property['property_type'] ?></p>
                  <p><strong>For:</strong> <?= $property['for_sell_rent'] ?></p>
                  <p><strong>Address:</strong> <?= $property['address'] ?></p>
                  <p><strong>Price:</strong> <?= number_format($property['listing_price'], 2) ?></p>
                  <p><strong>Features:</strong> <?= $property['sqft'] ?> sqft, <?= $property['bed'] ?> bed, <?= $property['bath'] ?> bath</p>
                  <p><strong>Images:</strong></p>
                  <details>
                    <summary>View Images</summary>
                    <?php foreach ($images as $image): ?>
                      <img src="../uploads/properties/<?= $image ?>" alt="" class="" style="max-width:200px;height:auto">
                    <?php endforeach; ?>
                  </details>

                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->