<?php
try {
    $sql = "SELECT * FROM properties ORDER BY created_at DESC LIMIT 12";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    } else {
        $properties = [];
    }
} catch (mysqli_sql_exception $e) {
    die("Error: " . $e->getMessage());
}
?>
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Properties</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?page=add-property" class="btn btn-sm btn-outline-dark">Add Property</a></li>
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
              <h5>Properties</h5>
            </div>
            <div class="card-body">
              <table class="table">
                <thead>
                  <tr>
                    <th></th>
                    <th>Images</th>
                    <th>Property</th>
                    <th>Address</th>
                    <th>Price (&#8358;)</th>
                    <th>Features</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $sn = 1; foreach ($properties as $property): $images = json_decode($property['images']); ?>
                    <tr>
                      <td><?= $sn++ ?></td>
                      <td>
                        <details>
                          <summary>Images</summary>
                          <?php foreach ($images as $image): ?>
                            <img src="../uploads/properties/<?= $image ?>" alt="" class="" style="max-width:200px;height:auto">
                          <?php endforeach; ?>
                        </details>
                      </td>
                      <td>
                        <h5 class=""><?= $property['title'] ?></h5>
                        <p class=""><?= $property['property_type'] ?></p>
                      </td>
                      <td>
                        <p class=""><?= $property['address'] ?></p>
                      </td>
                      <td>
                        <p class=""><?= number_format($property['listing_price']) ?></p>
                      </td>
                      <td>
                        <p class="">
                          <?= number_format($property['sqft']) ?> sqft <br>
                          <?= $property['bed'] ?> bed <br>
                          <?= $property['bath'] ?> bath <br>
                        </p>
                      </td>
                      <td>
                        <?php if ($property['owner_id'] == 0): ?>
                          <a href="?page=add-owner&id=<?= $property['id'] ?>" class="btn btn-warning btn-sm">Add Owner</a>
                          <a href="?page=edit-property&id=<?= $property['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                          <a href="?page=delete-property&id=<?= $property['id'] ?>" class="btn btn-danger btn-sm">&times;</a>
                        <?php else: ?>
                          <a href="?page=update-owner&id=<?= $property['id'] ?>" class="btn btn-outline-warning btn-sm">Owner</a>
                          <a href="?page=edit-property&id=<?= $property['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                          <a href="?page=delete-property&id=<?= $property['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                          <!-- <a href="?page=property&id=<?= $property['id'] ?>" class="btn btn-info btn-sm">View</a> -->
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->