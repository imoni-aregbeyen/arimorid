<?php
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'user') {
  echo '<div class="alert alert-danger">Access denied.</div>';
  exit();
}

// Load available services
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
)");
$addons = [];
try {
  $sql = "SELECT * FROM addons ORDER BY created_at DESC";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $addons[] = $row;
    }
  }
} catch (Exception $e) {
  echo '<div class="alert alert-danger">Error loading services: ' . $e->getMessage() . '</div>';
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_addons']) && isset($_POST['addon_ids'])) {
  $addon_ids = $_POST['addon_ids'];
  $days_arr = $_POST['days'] ?? [];
  $total = 0;
  $order_rows = [];
  $batch_id = uniqid('batch_' . $user_id . '_');
  foreach ($addon_ids as $addon_id) {
    $addon_id = intval($addon_id);
    $days = isset($days_arr[$addon_id]) ? max(1, intval($days_arr[$addon_id])) : 1;
    $addon = null;
    foreach ($addons as $a) {
      if ($a['id'] == $addon_id) {
        $addon = $a;
        break;
      }
    }
    if ($addon) {
      $price = floatval($addon['price']);
      $subtotal = $price * $days;
      $total += $subtotal;
      $order_rows[] = [
        'addon_id' => $addon_id,
        'service' => $addon['service'],
        'days' => $days,
        'price' => $price,
        'subtotal' => $subtotal
      ];
    }
  }
  if (empty($order_rows)) {
    echo '<div class="alert alert-danger">No valid services selected for ordering.</div>';
  } else {
    $vat = $total * 0.075;
    $grand_total = $total + $vat;
    // Store order details in session for payment
    $_SESSION['pending_addon_order'] = [
      'batch_id' => $batch_id,
      'user_id' => $user_id,
      'orders' => $order_rows,
      'total' => $total,
      'vat' => $vat,
      'grand_total' => $grand_total,
      'user_email' => $_SESSION['user_email'] ?? '',
    ];
    header('Location: ./?page=process-payment&type=addon');
    exit;
  }
}

// Fetch orders grouped by batch_id
$orders_by_batch = [];
$order_sql = "SELECT o.*, a.service FROM addon_orders o JOIN addons a ON o.addon_id = a.id WHERE o.user_id = ? ORDER BY o.created_at DESC";
$order_stmt = $conn->prepare($order_sql);
if ($order_stmt) {
  $order_stmt->bind_param("i", $user_id);
  $order_stmt->execute();
  $order_result = $order_stmt->get_result();
  while ($row = $order_result->fetch_assoc()) {
    $orders_by_batch[$row['batch_id']][] = $row;
  }
  $order_stmt->close();
}
?>
<!-- Order Modal Trigger Button -->
<div class="row justify-content-center mb-3">
  <div class="col-md-10 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderAddonsModal">
      Place New Order
    </button>
  </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderAddonsModal" tabindex="-1" aria-labelledby="orderAddonsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderAddonsModalLabel">Order Additional Services</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="orderAddonsForm" action="" method="POST">
          <input type="hidden" name="order_addons" value="1">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Select</th>
                  <th>Service</th>
                  <th>Price</th>
                  <th>Days</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($addons as $addon): ?>
                <tr>
                  <td><input type="checkbox" name="addon_ids[]" value="<?= $addon['id'] ?>" class="addon-check"></td>
                  <td><?= htmlspecialchars($addon['service']) ?></td>
                  <td class="addon-price" data-price="<?= $addon['price'] ?>">₦<?= number_format($addon['price'],2) ?></td>
                  <td><input type="number" name="days[<?= $addon['id'] ?>]" min="1" value="1" class="form-control days-input" style="width:80px;" disabled></td>
                  <td class="addon-subtotal">₦0.00</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="mb-3">
            <label>Total: </label>
            <span id="orderTotal">₦0.00</span>
            <br>
            <label>VAT (7.5%): </label>
            <span id="orderVAT">₦0.00</span>
            <br>
            <label>Grand Total: </label>
            <span id="orderGrandTotal">₦0.00</span>
          </div>
          <button type="submit" class="btn btn-success">Order Selected Services</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- User's placed orders table -->
<div class="row justify-content-center mt-4">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header">
        <h5>Your Service Orders</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($orders_by_batch)): ?>
          <?php $batch_num = 1; ?>
          <?php foreach ($orders_by_batch as $batch_id => $orders): ?>
            <?php
              // Calculate batch summary
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
                <?php foreach ($orders as $i => $order): ?>
                  <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border">
                      <div class="card-body">
                        <h6 class="card-title mb-2">Service: <?= htmlspecialchars($order['service']) ?></h6>
                        <ul class="list-unstyled mb-2">
                          <li><strong>Days:</strong> <?= $order['days'] ?></li>
                          <li><strong>Price:</strong> ₦<?= number_format($order['price'], 2) ?></li>
                          <li><strong>Subtotal:</strong> ₦<?= number_format($order['subtotal'], 2) ?></li>
                        </ul>
                        <div class="mb-2">
                          <span class="me-2"><strong>Status:</strong> <?php if ($order['status'] == 1): ?><span class="badge bg-success">Settled</span><?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?></span>
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
<script>
document.querySelectorAll('.addon-check').forEach(function(checkbox) {
  checkbox.addEventListener('change', function() {
    var row = this.closest('tr');
    var daysInput = row.querySelector('.days-input');
    daysInput.disabled = !this.checked;
    if (!this.checked) {
      daysInput.value = 1;
      row.querySelector('.addon-subtotal').textContent = '₦0.00';
    }
    calculateTotals();
  });
});
document.querySelectorAll('.days-input').forEach(function(input) {
  input.addEventListener('input', function() {
    calculateTotals();
  });
});
function calculateTotals() {
  var total = 0;
  document.querySelectorAll('.days-input').forEach(function(input) {
    var row = input.closest('tr');
    var checkbox = row.querySelector('.addon-check');
    var price = parseFloat(row.querySelector('.addon-price').getAttribute('data-price')) || 0;
    var days = parseInt(input.value) || 1;
    var subtotal = 0;
    if (checkbox.checked) {
      subtotal = price * days;
      total += subtotal;
    }
    row.querySelector('.addon-subtotal').textContent = '₦' + subtotal.toLocaleString(undefined, {minimumFractionDigits:2});
  });
  var vat = total * 0.075;
  var grandTotal = total + vat;
  document.getElementById('orderTotal').textContent = '₦' + total.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('orderVAT').textContent = '₦' + vat.toLocaleString(undefined, {minimumFractionDigits:2});
  document.getElementById('orderGrandTotal').textContent = '₦' + grandTotal.toLocaleString(undefined, {minimumFractionDigits:2});
}
</script>
