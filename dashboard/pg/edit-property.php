<?php
$id = $_GET['id'] ?? null;
if ($id) {
  $sql = "SELECT * FROM properties WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $property = $result->fetch_assoc();
    $images = json_decode($property['images']);
  } else {
    echo "<script>alert('Property not found');</script>";
    exit;
  }
} else {
  echo "<script>alert('Invalid property ID');</script>";
  exit;
}
?>
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Edit Property</h5>
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
              <h5><?= $property['title'] ?></h5>
            </div>
            <div class="card-body">
              <form action="./ac/action-edit-property.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $property['id'] ?>">
                <input type="hidden" name="propertyType" value="<?= $property['property_type'] ?>">
                <div class="form-group">
                  <label for="forSellRent">For Sell / For Rent</label>
                  <select class="form-control" id="forSellRent" name="forSellRent" required>
                    <option value="Sell" <?= $property['for_sell_rent'] === 'Sell' ? 'selected' : '' ?>>For Sell</option>
                    <option value="Rent" <?= $property['for_sell_rent'] === 'Rent' ? 'selected' : '' ?>>For Rent</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="images">Image(s)</label>
                  <?php if ($images): ?>
                    <details>
                      <summary>Current Images</summary>
                      <?php $index = 0; foreach ($images as $image): ?>
                        <div class="d-inline-block p-1">
                          <img src="../uploads/properties/<?= $image ?>" alt="" class="" style="max-width:200px;height:auto">
                          <a href="./ac/action-delete-property-image.php?id=<?= $property['id'] ?>&image=<?= $image ?>&index=<?= $index ?>" class="btn btn-sm btn-danger">x</a>
                        </div>
                      <?php $index++; endforeach; ?>
                    </details>
                  <?php endif; ?>
                  <input type="file" class="form-control" id="images" name="images[]" multiple>
                </div>
                <div class="form-group">
                  <label for="listingPrice">Listing Price</label>
                  <input type="text" class="form-control" id="listingPrice" name="listingPrice" value="<?= $property['listing_price'] ?>" required>
                </div>
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" class="form-control" id="title" name="title" value="<?= $property['title'] ?>" required>
                </div>
                <div class="form-group">
                  <label for="address">Details</label>
                  <input type="text" class="form-control" id="address" name="address" value="<?= $property['address'] ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Property</button>
              </form>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->