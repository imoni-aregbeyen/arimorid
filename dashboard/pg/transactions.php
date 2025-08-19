<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../?page=login');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch transactions (bookings/payments) for the user
$stmt = $conn->prepare("SELECT b.*, a.title as apartment_title FROM bookings b LEFT JOIN service_apartments a ON b.apartment_id = a.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="container mt-4">
    <h2>My Transactions</h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Apartment</th>
                    <th>Days</th>
                    <th>Total Cost</th>
                    <th>Payment Ref</th>
                    <!-- <th>Status</th> -->
                    <th>Date</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="7" class="text-center">No transactions found</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $i => $txn): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($txn['apartment_title']) ?></td>
                        <td><?= htmlspecialchars($txn['days']) ?></td>
                        <td>₦<?= number_format($txn['total_cost'], 2) ?></td>
                        <td><?= htmlspecialchars($txn['payment_reference']) ?></td>
                        <!-- <td><span class="badge bg-<?= $txn['status']==='confirmed'?'success':($txn['status']==='pending'?'warning':'secondary') ?>"><?= ucfirst($txn['status']) ?></span></td> -->
                        <td><?= date('M j, Y', strtotime($txn['created_at'])) ?></td>
                        <td><?= date('h:i A', strtotime($txn['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
