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
// Stats and filter logic for admin
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

// Date filter defaults
$filter_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$order_filter_sql = "SELECT * FROM addon_orders WHERE MONTH(created_at) = $filter_month AND YEAR(created_at) = $filter_year";
$order_filter_result = $conn->query($order_filter_sql);
$total_order = 0;
$total_vat = 0;
if ($order_filter_result && $order_filter_result->num_rows > 0) {
  while ($row = $order_filter_result->fetch_assoc()) {
    $total_order += $row['grand_total'];
    $total_vat += $row['vat'];
  }
}

// For filter dropdowns
$months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
$years = range(date('Y')-3, date('Y')+1);

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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewServicesModal">
              View Services
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServiceModal">
              Add Service
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<?php if ($is_admin): ?>
<div class="row mt-3 mb-2">
  <div class="col-md-3">
    <div class="card text-bg-primary mb-3">
      <div class="card-body">
        <h6 class="card-title">Total Orders</h6>
        <p class="card-text fs-5">₦<?= number_format($total_order, 2) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-success mb-3">
      <div class="card-body">
        <h6 class="card-title">Total VAT</h6>
        <p class="card-text fs-5">₦<?= number_format($total_vat, 2) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <form method="get" class="d-flex align-items-end flex-wrap gap-2">
      <input type="hidden" name="page" value="addons">
      <div>
        <label for="month" class="form-label mb-0">Month</label>
        <select name="month" id="month" class="form-select">
          <?php foreach ($months as $num => $name): ?>
            <option value="<?= $num ?>"<?= $filter_month == $num ? ' selected' : '' ?>><?= $name ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="year" class="form-label mb-0">Year</label>
        <select name="year" id="year" class="form-select">
          <?php foreach ($years as $yr): ?>
            <option value="<?= $yr ?>"<?= $filter_year == $yr ? ' selected' : '' ?>><?= $yr ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary ms-2">Filter</button>
        <a href="./?page=addons" class="btn btn-outline-secondary ms-1">Reset</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Modal: View Services -->
<div class="modal fade" id="viewServicesModal" tabindex="-1" aria-labelledby="viewServicesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewServicesModalLabel">All Additional Services</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Picture</th>
                <th>Service</th>
                <th>Description</th>
                <th>Price (per day)</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($addons)) : ?>
                <?php foreach ($addons as $index => $addon) : ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                      <?php if (!empty($addon['picture'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($addon['picture']) ?>" style="width:50px;height:50px;object-fit:cover;" alt="Service Picture">
                      <?php else: ?>
                        <span class="text-muted">No image</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($addon['service']) ?></td>
                    <td><?= htmlspecialchars($addon['description'] ?? '') ?></td>
                    <td>₦<?= number_format($addon['price'], 2) ?></td>
                    <td>
                      <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="service_id" value="<?= $addon['id'] ?>">
                        <button type="submit" name="delete_service" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
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
                        <form action="" method="POST" enctype="multipart/form-data">
                          <div class="modal-body">
                            <input type="hidden" name="service_id" value="<?= $addon['id'] ?>">
                            <div class="mb-3">
                              <label for="service<?= $addon['id'] ?>" class="form-label">Service Name</label>
                              <input type="text" class="form-control" id="service<?= $addon['id'] ?>" name="service" value="<?= htmlspecialchars($addon['service']) ?>" required>
                            </div>
                            <div class="mb-3">
                              <label for="description<?= $addon['id'] ?>" class="form-label">Description</label>
                              <textarea class="form-control" id="description<?= $addon['id'] ?>" name="description" rows="2"><?= htmlspecialchars($addon['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                              <label for="picture<?= $addon['id'] ?>" class="form-label">Picture</label>
                              <input type="file" class="form-control" id="picture<?= $addon['id'] ?>" name="picture" accept="image/*">
                              <?php if (!empty($addon['picture'])): ?>
                                <div class="mt-2">
                                  <small class="text-muted">Current picture:</small><br>
                                  <img src="../uploads/<?= htmlspecialchars($addon['picture']) ?>" style="width:80px;height:80px;object-fit:cover;" alt="Current Service Picture">
                                  <br>
                                  <small class="text-muted">Leave empty to keep current picture</small>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="mb-3">
                              <label for="price<?= $addon['id'] ?>" class="form-label">Price (per unit)</label>
                              <input type="number" step="0.01" class="form-control" id="price<?= $addon['id'] ?>" name="price" value="<?= htmlspecialchars($addon['price']) ?>" required>
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
                  <td colspan="6" class="text-center">No services available</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal: View Services end -->

<?php
// Ensure addon_orders table exists
$conn->query("CREATE TABLE IF NOT EXISTS addon_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id VARCHAR(32) NOT NULL,
  user_id INT NOT NULL,
  addon_id INT NOT NULL,
  days INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  vat DECIMAL(10,2) NOT NULL,
  grand_total DECIMAL(10,2) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'], $_POST['order_id'])) {
  $order_id = intval($_POST['order_id']);
  $new_status = ($_POST['new_status'] == '1') ? 1 : 0;
  $stmt = $conn->prepare("UPDATE addon_orders SET status=? WHERE id=?");
  if ($stmt) {
    $stmt->bind_param("ii", $new_status, $order_id);
    if ($stmt->execute()) {
      echo '<div class="alert alert-success">Order status updated.</div>';
    } else {
      echo '<div class="alert alert-danger">Error updating status: ' . htmlspecialchars($stmt->error) . '</div>';
    }
    $stmt->close();
  } else {
    echo '<div class="alert alert-danger">Error preparing statement: ' . htmlspecialchars($conn->error) . '</div>';
  }
}

// Fetch all orders grouped by batch
$orders_by_batch = [];
$order_sql = "SELECT o.*, a.service, u.name FROM addon_orders o JOIN addons a ON o.addon_id = a.id JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
$order_result = $conn->query($order_sql);
if ($order_result && $order_result->num_rows > 0) {
  while ($row = $order_result->fetch_assoc()) {
    $orders_by_batch[$row['batch_id']][] = $row;
  }
}
?>

<div class="row justify-content-center mt-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5>All Service Orders</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($orders_by_batch)): ?>
          <?php $batch_num = 1; ?>
          <?php foreach ($orders_by_batch as $batch_id => $orders): ?>
            <?php
              $batch_total = 0;
              foreach ($orders as $order) {
                $batch_total += $order['subtotal'];
              }
              $batch_vat = $batch_total * 0.075;
              $batch_grand_total = $batch_total + $batch_vat;
            ?>
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                <h6 class="mb-0">Order Batch #<?= $batch_num ?> <span class="text-muted" style="font-size:0.9em;">(<?= htmlspecialchars($batch_id) ?>)</span></h6>
                <div class="bg-light p-2 rounded ms-2 mb-2 mb-md-0">
                  <span class="me-3"><strong>Total:</strong> ₦<?= number_format($batch_total, 2) ?></span>
                  <span class="me-3"><strong>VAT (7.5%):</strong> ₦<?= number_format($batch_vat, 2) ?></span>
                  <span><strong>Grand Total:</strong> ₦<?= number_format($batch_grand_total, 2) ?></span>
                </div>
              </div>
              <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                  <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border">
                      <div class="card-body">
                        <h6 class="card-title mb-2">Service: <?= htmlspecialchars($order['service']) ?></h6>
                        <ul class="list-unstyled mb-2">
                          <li><strong>User:</strong> <?= htmlspecialchars($order['name']) ?></li>
                          <li><strong>Units:</strong> <?= $order['days'] ?></li>
                          <li><strong>Price:</strong> ₦<?= number_format($order['price'], 2) ?></li>
                          <li><strong>Subtotal:</strong> ₦<?= number_format($order['subtotal'], 2) ?></li>
                        </ul>
                        <div class="mb-2">
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="update_order_status" value="1">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <span class="me-2"><strong>Status:</strong> <?php if ($order['status'] == 1): ?><span class="badge bg-success">Settled</span><?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?></span>
                            <select name="new_status" class="form-select form-select-sm d-inline w-auto me-2">
                              <option value="0"<?= $order['status'] == 0 ? ' selected' : '' ?>>Pending</option>
                              <option value="1"<?= $order['status'] == 1 ? ' selected' : '' ?>>Settled</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                          </form>
                        </div>
                        <small class="text-muted">Ordered: <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></small>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php $batch_num++; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center">No orders placed yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

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
            <th>Picture</th>
            <th>Service</th>
            <th>Description</th>
            <th>Price (per unit)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($addons)) : ?>
            <?php foreach ($addons as $index => $addon) : ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td>
                  <?php if (!empty($addon['picture'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($addon['picture']) ?>" style="width:50px;height:50px;object-fit:cover;" alt="Service Picture">
                  <?php else: ?>
                    <span class="text-muted">No image</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($addon['service']) ?></td>
                <td><?= htmlspecialchars($addon['description'] ?? '') ?></td>
                <td>₦<?= number_format($addon['price'], 2) ?></td>
                <td>
                  <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="service_id" value="<?= $addon['id'] ?>">
                    <button type="submit" name="delete_service" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                  </form>
                  <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal<?= $addon['id'] ?>">Edit</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="6" class="text-center">No services available</td>
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
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="service" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="service" name="service" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label for="picture" class="form-label">Picture</label>
            <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
          </div>
          <div class="mb-3">
            <label for="price" class="form-label">Price (per unit)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- add service modal end -->