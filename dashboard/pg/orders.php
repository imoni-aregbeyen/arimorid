<?php
if ($user_role === 'owner') {
    // select from bookings where apartment_id = an id in service_apartments where owner_id = $user_id
    $sql = "SELECT * FROM bookings WHERE apartment_id IN (SELECT id FROM service_apartments WHERE owner_id = $user_id) ORDER BY created_at DESC"; // id, user_id, apartment_id, days, addons, total_daily_charge, caution_fee, addons_cost, vat, total_cost, payment_reference, created_at
} elseif ($user_role === 'customer') {
    $sql = "SELECT * FROM bookings WHERE user_id = $user_id ORDER BY created_at DESC"; // id, user_id, apartment_id, days, addons, total_daily_charge, caution_fee, addons_cost, vat, total_cost, payment_reference, created_at
} elseif ($user_role === 'admin') {
    $sql = "SELECT * FROM bookings ORDER BY created_at DESC"; // id, user_id, apartment_id, days, addons, total_daily_charge, caution_fee, addons_cost, vat, total_cost, payment_reference, created_at
}
$result = $conn->query($sql);
$bookings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
if (!$bookings) {
    echo "<script>alert('No bookings found.');</script>";
    exit;
}
?>      
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Orders</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <!-- <li class="breadcrumb-item"><a href="?page=add-property" class="">Add Property</a></li> -->
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
              <h5>Orders</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User ID</th>
                      <th>Apartment ID</th>
                      <th>Days</th>
                      <th>Addons</th>
                      <th>Total Daily Charge</th>
                      <th>Caution Fee</th>
                      <th>Addons Fee</th>
                      <th>VAT</th>
                      <th>Total Cost</th>
                      <th>Payment Reference</th>
                      <th>Created At</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($bookings as $index => $booking): ?>
                    <tr>
                      <td><?php echo $index + 1; ?></td>
                      <td><?php echo htmlspecialchars($booking['user_id']); ?></td>
                      <td><?php echo htmlspecialchars($booking['apartment_id']); ?></td>
                      <td><?php echo htmlspecialchars($booking['days']); ?></td>
                      <td><?php echo htmlspecialchars($booking['addons']); ?></td>
                      <td>₦<?php echo number_format(htmlspecialchars($booking['total_daily_charge']), 2); ?></td>
                      <td>₦<?php echo number_format(htmlspecialchars($booking['caution_fee']), 2); ?></td>
                      <td>₦<?php echo number_format(htmlspecialchars($booking['addons_cost']), 2); ?></td>
                      <td>₦<?php echo number_format(htmlspecialchars($booking['vat']), 2); ?></td>
                      <td>₦<?php echo number_format(htmlspecialchars($booking['total_cost']), 2); ?></td>
                      <td><?php echo htmlspecialchars($booking['payment_reference']); ?></td>
                      <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($booking['created_at']))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->