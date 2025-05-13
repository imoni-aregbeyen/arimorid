<?php

try {
  $sql = "SELECT * FROM addons ORDER BY created_at DESC";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $addons[] = $row;
    }
  } else {
    $addons = [];
  }
} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete_service'])) {
    $service_id = intval($_POST['service_id']); // Ensure service_id is an integer

    // Delete service from database
    $stmt = $conn->prepare("DELETE FROM addons WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) { // Check if a row was actually deleted
      echo "<script>alert('Service deleted successfully.');location.href='./?page=addons'</script>";
      exit();
    } else {
      echo "<script>alert('Error deleting service or service not found.');</script>";
    }
    $stmt->close();
  } elseif (isset($_POST['edit_service'])) {
    $service_id = intval($_POST['service_id']);
    $service = $_POST['service'];
    $price = $_POST['price'];

    // Validate inputs
    if (empty($service) || empty($price)) {
      echo "<script>alert('Please fill in all fields.');</script>";
    } else {
      // Update service in database
      $stmt = $conn->prepare("UPDATE addons SET service = ?, price = ? WHERE id = ?");
      $stmt->bind_param("sdi", $service, $price, $service_id);
      if ($stmt->execute()) {
        echo "<script>alert('Service updated successfully.');location.href='./?page=addons'</script>";
        exit();
      } else {
        echo "<script>alert('Error updating service.');</script>";
      }
      $stmt->close();
    }
  } else {
    $service = $_POST['service'];
    $price = $_POST['price'];

    // Validate inputs
    if (empty($service) || empty($price)) {
      echo "<script>alert('Please fill in all fields.');</script>";
    } else {
      // Check for duplicate service
      $stmt = $conn->prepare("SELECT COUNT(*) FROM addons WHERE service = ?");
      $stmt->bind_param("s", $service);
      $stmt->execute();
      $stmt->bind_result($count);
      $stmt->fetch();
      $stmt->close();

      if ($count > 0) {
        echo "<script>alert('Service already exists.');</script>";
      } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO addons (service, price) VALUES (?, ?)");
        $stmt->bind_param("sd", $service, $price);
        if ($stmt->execute()) {
          echo "<script>alert('Service added successfully.');location.href='./?page=addons'</script>";
          exit();
        } else {
          echo "<script>alert('Error adding service.');</script>";
        }
      }
    }
  }
}

?>
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Additional Services</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item">
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    Add Service
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- services table within card -->
      <div class="card">
        <div class="card-header">
          <h5>Services</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Service</th>
                  <th>Price (per day)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($addons)) : ?>
                  <?php foreach ($addons as $index => $addon) : ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars($addon['service']) ?></td>
                      <td><?= htmlspecialchars($addon['price']) ?></td>
                      <td>
                        <form action="" method="POST" style="display:inline;">
                          <input type="hidden" name="service_id" value="<?= $addon['id'] ?>">
                          <button type="submit" name="delete_service" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal<?= $addon['id'] ?>">Edit</button>
                      </td>
                    </tr>
                    <!-- edit service modal -->
                    <div class="modal fade" id="editServiceModal<?= $addon['id'] ?>" tabindex="-1" aria-labelledby="editServiceModalLabel<?= $addon['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editServiceModalLabel<?= $addon['id'] ?>">Edit Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form action="" method="POST">
                            <div class="modal-body">
                              <input type="hidden" name="service_id" value="<?= $addon['id'] ?>">
                              <div class="mb-3">
                                <label for="service<?= $addon['id'] ?>" class="form-label">Service Name</label>
                                <input type="text" class="form-control" id="service<?= $addon['id'] ?>" name="service" value="<?= htmlspecialchars($addon['service']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label for="price<?= $addon['id'] ?>" class="form-label">Price (per day)</label>
                                <input type="number" class="form-control" id="price<?= $addon['id'] ?>" name="price" value="<?= htmlspecialchars($addon['price']) ?>" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" name="edit_service" class="btn btn-primary">Save Changes</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <!-- edit service modal end -->
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="4" class="text-center">No services available</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- services table end -->

      <!-- add service modal -->
      <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addServiceModalLabel">Add Service</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                <div class="mb-3">
                  <label for="service" class="form-label">Service Name</label>
                  <input type="text" class="form-control" id="service" name="service" required>
                </div>
                <div class="mb-3">
                  <label for="price" class="form-label">Price (per day)</label>
                  <input type="number" class="form-control" id="price" name="price" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add Service</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- add service modal end -->