<?php

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
if (!$user_id || $user_role !== 'owner') {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}
// Get total earnings
$earnings_sql = "SELECT SUM(b.owner_total) as earnings FROM bookings b JOIN service_apartments a ON b.apartment_id = a.id WHERE a.owner_id = $user_id";
$earnings_result = $conn->query($earnings_sql);
$total_earnings = $earnings_result->fetch_assoc()['earnings'] ?? 0;
// Get total withdrawn
$withdrawn_sql = "SELECT SUM(amount) as withdrawn FROM withdrawals WHERE owner_id = $user_id";
$withdrawn_result = $conn->query($withdrawn_sql);
$total_withdrawn = $withdrawn_result->fetch_assoc()['withdrawn'] ?? 0;
// Calculate post-withdrawal balance
$balance = $total_earnings - $total_withdrawn;
// Check for pending withdrawal
$pending_withdrawal = false;
$pending_check = $conn->query("SELECT id FROM withdrawals WHERE owner_id = $user_id AND status = 0 LIMIT 1");
if ($pending_check && $pending_check->num_rows > 0) {
    $pending_withdrawal = true;
}
// Handle withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_amount']) && !$pending_withdrawal) {
    $amount = floatval($_POST['withdraw_amount']);
    if ($amount > 0 && $amount <= $balance) {
        $stmt = $conn->prepare("INSERT INTO withdrawals (owner_id, amount, status) VALUES (?, ?, 0)");
        $stmt->bind_param("id", $user_id, $amount);
        $stmt->execute();
        echo '<div class="alert alert-success">Withdrawal request submitted!</div>';
        $balance -= $amount;
        $total_withdrawn += $amount;
    } else {
        echo '<div class="alert alert-danger">Invalid withdrawal amount.</div>';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $pending_withdrawal) {
    echo '<div class="alert alert-danger">You have a pending withdrawal. Please wait for it to be processed before requesting another.</div>';
}
// List all withdrawals
$withdrawals = $conn->query("SELECT * FROM withdrawals WHERE owner_id = $user_id ORDER BY created_at DESC");
?>
<div class="container py-5">
    <h2>Withdraw Earnings</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5>Total Earnings: ₦<?= number_format($total_earnings, 2) ?></h5>
            <h5>Total Withdrawn: ₦<?= number_format($total_withdrawn, 2) ?></h5>
            <h5>Available Balance: ₦<?= number_format($balance, 2) ?></h5>
            <?php if ($pending_withdrawal): ?>
                <div class="alert alert-warning">You have a pending withdrawal. Please wait for it to be processed before requesting another.</div>
            <?php else: ?>
            <form method="post" class="mt-3">
                <div class="mb-3">
                    <label for="withdraw_amount" class="form-label">Withdraw Amount</label>
                    <input type="number" step="0.01" min="1" max="<?= $balance ?>" class="form-control" id="withdraw_amount" name="withdraw_amount" required>
                </div>
                <button type="submit" class="btn btn-primary">Request Withdrawal</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <h4>Withdrawal History</h4>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($withdrawals->num_rows > 0): ?>
                        <?php while($w = $withdrawals->fetch_assoc()): ?>
                            <tr>
                                <td><?= $w['id'] ?></td>
                                <td>₦<?= number_format($w['amount'], 2) ?></td>
                                <td><?= date('M j, Y h:i A', strtotime($w['created_at'])) ?></td>
                                <td>
                                    <?php
                                    if (!isset($w['status']) || $w['status'] == 0) {
                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Completed</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">No withdrawals yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
