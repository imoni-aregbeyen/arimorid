<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../?page=login');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch all transaction types and combine them

// 1. Fetch booking transactions
$bookings_stmt = $conn->prepare("
    SELECT 
        b.id,
        'booking' as type,
        a.title as description,
        b.days,
        b.total_cost as amount,
        b.payment_reference,
        b.created_at,
        'completed' as status
    FROM bookings b 
    LEFT JOIN service_apartments a ON b.apartment_id = a.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$bookings_stmt->bind_param("i", $user_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$booking_transactions = $bookings_result->fetch_all(MYSQLI_ASSOC);
$bookings_stmt->close();

// 2. Fetch additional service transactions
if ($user_role === 'admin') {
    $services_stmt = $conn->prepare("
        SELECT 
            ao.id,
            'additional_service' as type,
            a.service as description,
            ao.days,
            ao.grand_total as amount,
            ao.batch_id as payment_reference,
            ao.created_at,
            CASE 
                WHEN ao.status = 1 THEN 'settled'
                ELSE 'pending'
            END as status
        FROM addon_orders ao
        LEFT JOIN addons a ON ao.addon_id = a.id 
        ORDER BY ao.created_at DESC
    ");
    $services_stmt->execute();
    $services_result = $services_stmt->get_result();
    $service_transactions = $services_result->fetch_all(MYSQLI_ASSOC);
    $services_stmt->close();
} else {
    $services_stmt = $conn->prepare("
        SELECT 
            ao.id,
            'additional_service' as type,
            a.service as description,
            ao.days,
            ao.grand_total as amount,
            ao.batch_id as payment_reference,
            ao.created_at,
            CASE 
                WHEN ao.status = 1 THEN 'settled'
                ELSE 'pending'
            END as status
        FROM addon_orders ao
        LEFT JOIN addons a ON ao.addon_id = a.id 
        WHERE ao.user_id = ? 
        ORDER BY ao.created_at DESC
    ");
    $services_stmt->bind_param("i", $user_id);
    $services_stmt->execute();
    $services_result = $services_stmt->get_result();
    $service_transactions = $services_result->fetch_all(MYSQLI_ASSOC);
    $services_stmt->close();
}

// 3. Fetch withdrawal transactions
$withdrawal_transactions = [];
if ($user_role === 'admin') {
    $withdrawals_stmt = $conn->prepare("
        SELECT 
            w.id,
            'withdrawal' as type,
            CONCAT('Earnings Withdrawal - ', u.name) as description,
            1 as days,
            w.amount,
            '' as payment_reference,
            w.created_at,
            CASE 
                WHEN w.status = 1 THEN 'completed'
                ELSE 'pending'
            END as status
        FROM withdrawals w
        LEFT JOIN users u ON w.owner_id = u.id
        ORDER BY w.created_at DESC
    ");
    $withdrawals_stmt->execute();
    $withdrawals_result = $withdrawals_stmt->get_result();
    $withdrawal_transactions = $withdrawals_result->fetch_all(MYSQLI_ASSOC);
    $withdrawals_stmt->close();
} elseif ($user_role === 'owner') {
    $withdrawals_stmt = $conn->prepare("
        SELECT 
            id,
            'withdrawal' as type,
            'Earnings Withdrawal' as description,
            1 as days,
            amount,
            '' as payment_reference,
            created_at,
            CASE 
                WHEN status = 1 THEN 'completed'
                ELSE 'pending'
            END as status
        FROM withdrawals 
        WHERE owner_id = ? 
        ORDER BY created_at DESC
    ");
    $withdrawals_stmt->bind_param("i", $user_id);
    $withdrawals_stmt->execute();
    $withdrawals_result = $withdrawals_stmt->get_result();
    $withdrawal_transactions = $withdrawals_result->fetch_all(MYSQLI_ASSOC);
    $withdrawals_stmt->close();
}

// Combine all transactions
$all_transactions = array_merge($booking_transactions, $service_transactions, $withdrawal_transactions);

// Sort all transactions by date (newest first)
usort($all_transactions, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<div class="container mt-4">
    <h2>My Transactions</h2>
    
    <!-- Transaction Type Filter -->
    <div class="row mb-3">
        <div class="col-md-6">
            <select id="transactionTypeFilter" class="form-select">
                <option value="all">All Transactions</option>
                <option value="booking">Bookings</option>
                <option value="additional_service">Additional Services</option>
                <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                <option value="withdrawal">Withdrawals</option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Days</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($all_transactions)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No transactions found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($all_transactions as $i => $txn): ?>
                    <tr class="transaction-row" data-type="<?= htmlspecialchars($txn['type']) ?>">
                        <td><?= $i + 1 ?></td>
                        <td>
                            <?php 
                            $type_badge = [
                                'booking' => 'primary',
                                'additional_service' => 'success', 
                                'withdrawal' => 'warning'
                            ];
                            $type_labels = [
                                'booking' => 'Booking',
                                'additional_service' => 'Service',
                                'withdrawal' => 'Withdrawal'
                            ];
                            ?>
                            <span class="badge bg-<?= $type_badge[$txn['type']] ?>">
                                <?= $type_labels[$txn['type']] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($txn['description']) ?></td>
                        <td>
                            <?php if ($txn['type'] === 'withdrawal'): ?>
                                -
                            <?php else: ?>
                                <?= htmlspecialchars($txn['days']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($txn['type'] === 'withdrawal'): ?>
                                <span class="text-danger">-₦<?= number_format($txn['amount'], 2) ?></span>
                            <?php else: ?>
                                <span class="text-success">+₦<?= number_format($txn['amount'], 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($txn['payment_reference'])): ?>
                                <small><?= htmlspecialchars($txn['payment_reference']) ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status_badge = [
                                'completed' => 'success',
                                'settled' => 'success',
                                'pending' => 'warning',
                                'confirmed' => 'info'
                            ];
                            $status_text = $txn['status'];
                            if ($txn['status'] === 'settled') {
                                $status_text = 'completed';
                            }
                            ?>
                            <span class="badge bg-<?= $status_badge[$txn['status']] ?? 'secondary' ?>">
                                <?= ucfirst($status_text) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($txn['created_at'])) ?></td>
                        <td><?= date('h:i A', strtotime($txn['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('transactionTypeFilter');
    const transactionRows = document.querySelectorAll('.transaction-row');
    
    filterSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        transactionRows.forEach(row => {
            if (selectedType === 'all' || row.getAttribute('data-type') === selectedType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>