<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}
// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'], $_POST['new_status'])) {
    $wid = intval($_POST['withdrawal_id']);
    $new_status = intval($_POST['new_status']);
    $stmt = $conn->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $wid);
    $stmt->execute();
    echo '<div class="alert alert-success">Withdrawal status updated.</div>';
}
$withdrawals = $conn->query("SELECT w.*, u.name as owner_name, u.email as owner_email, u.account_number, u.bank_name, u.account_name FROM withdrawals w JOIN users u ON w.owner_id = u.id ORDER BY w.created_at DESC");
?>
<div class="container py-5">
    <h2>Withdrawal Requests</h2>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Account Details</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($withdrawals->num_rows > 0): ?>
                        <?php while($w = $withdrawals->fetch_assoc()): ?>
                            <tr>
                                <td><?= $w['id'] ?></td>
                                <td><?= htmlspecialchars($w['owner_name']) ?></td>
                                <td><?= htmlspecialchars($w['owner_email']) ?></td>
                                <td>₦<?= number_format($w['amount'], 2) ?></td>
                                <td><?= date('M j, Y h:i A', strtotime($w['created_at'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($w['account_number']) ?></strong><br>
                                    <?= htmlspecialchars($w['bank_name']) ?><br>
                                    <?= htmlspecialchars($w['account_name']) ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="withdrawal_id" value="<?= $w['id'] ?>">
                                        <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="0" <?= (!isset($w['status']) || $w['status'] == 0) ? 'selected' : '' ?>>Pending</option>
                                            <option value="1" <?= (isset($w['status']) && $w['status'] == 1) ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                    <?php
                                    if (!isset($w['status']) || $w['status'] == 0) {
                                        echo '<span class="badge bg-warning text-dark ms-2">Pending</span>';
                                    } else {
                                        echo '<span class="badge bg-success ms-2">Completed</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No withdrawal requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
