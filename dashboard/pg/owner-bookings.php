<?php
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if (!$owner_id) {
    echo '<div class="alert alert-danger">Invalid owner ID.</div>';
    exit;
}
// Get owner info
$owner = $conn->query("SELECT * FROM users WHERE id = $owner_id AND role = 'owner'")->fetch_assoc();
if (!$owner) {
    echo '<div class="alert alert-danger">Owner not found.</div>';
    exit;
}
// Get bookings for owner for selected month/year
$sql = "SELECT b.*, a.title as apartment_title FROM bookings b JOIN service_apartments a ON b.apartment_id = a.id WHERE a.owner_id = $owner_id AND YEAR(b.created_at) = $selected_year AND MONTH(b.created_at) = $selected_month ORDER BY b.created_at DESC";
$result = $conn->query($sql);
// Get sum total for bookings
$total_sum = 0;
if ($result && $result->num_rows > 0) {
    foreach ($result as $row) {
        $total_sum += $row['total_cost'];
    }
    // Reset result pointer for table rendering
    $result->data_seek(0);
}
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Bookings for <?= htmlspecialchars($owner['name']) ?></h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="./?page=owners">Owners</a></li>
          <li class="breadcrumb-item">Bookings</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- Filter UI -->
<div class="row mb-4">
  <form method="GET" class="d-flex align-items-center">
    <input type="hidden" name="page" value="owner-bookings">
    <input type="hidden" name="id" value="<?= $owner_id ?>">
    <label for="month" class="me-2">Month:</label>
    <select name="month" id="month" class="form-select me-2" style="width:auto;">
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
      <?php endfor; ?>
    </select>
    <label for="year" class="me-2">Year:</label>
    <select name="year" id="year" class="form-select me-2" style="width:auto;">
      <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
        <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
  </form>
</div>
<div class="card">
  <div class="card-header">
    <h5>Bookings for <?= htmlspecialchars($owner['name']) ?> (<?= date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?>)</h5>
    <div class="mt-2"><strong>Sum Total: ₦<?= number_format($total_sum, 2) ?></strong></div>
  </div>
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Apartment</th>
          <th>Days</th>
          <th>Total Cost</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php $sn = 1; if ($result && $result->num_rows > 0): while ($booking = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $sn++ ?></td>
          <td><?= htmlspecialchars($booking['apartment_title']) ?></td>
          <td><?= $booking['days'] ?></td>
          <td>₦<?= number_format($booking['total_cost'], 2) ?></td>
          <td><?= date('M j, Y', strtotime($booking['created_at'])) ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" class="text-center">No bookings found for this period.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
