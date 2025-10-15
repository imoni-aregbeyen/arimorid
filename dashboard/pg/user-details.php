<?php
// id, name, email, password, role, verified, phone, dob, address, id_type, id_number, id_document, account_number, bank_name, account_name,
// emgc_name, emgc_address, emgc_phone, emgc_email, emgc_relationship, created_at
$id = (int) $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
    $role = $conn->real_escape_string($_POST['role'] ?? '');
        $verified = isset($_POST['verified']) ? 1 : 0;
    $dob = $conn->real_escape_string($_POST['dob'] ?? '');
    $occupation = $conn->real_escape_string($_POST['occupation'] ?? '');
    $address = $conn->real_escape_string($_POST['address']);
    $id_type = $conn->real_escape_string($_POST['id_type']);
    $id_number = $conn->real_escape_string($_POST['id_number']);
    $account_number = $conn->real_escape_string($_POST['account_number'] ?? '');
    $bank_name = $conn->real_escape_string($_POST['bank_name'] ?? '');
    $account_name = $conn->real_escape_string($_POST['account_name'] ?? '');
        $emgc_name = $conn->real_escape_string($_POST['emgc_name']);
        $emgc_address = $conn->real_escape_string($_POST['emgc_address']);
        $emgc_phone = $conn->real_escape_string($_POST['emgc_phone']);
        $emgc_email = $conn->real_escape_string($_POST['emgc_email']);
        $emgc_relationship = $conn->real_escape_string($_POST['emgc_relationship']);
    $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', role='$role', verified='$verified', dob='$dob', address='$address', occupation='$occupation', id_type='$id_type', id_number='$id_number', account_number='$account_number', bank_name='$bank_name', account_name='$account_name', emgc_name='$emgc_name', emgc_address='$emgc_address', emgc_phone='$emgc_phone', emgc_email='$emgc_email', emgc_relationship='$emgc_relationship' WHERE id=$id";
        if ($conn->query($sql)) {
            echo '<div class="alert alert-success">User updated successfully.</div>';
        } else {
            echo '<div class="alert alert-danger">Error updating user: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }
    if (isset($_POST['delete_user'])) {
        $sql = "DELETE FROM users WHERE id=$id";
        if ($conn->query($sql)) {
            echo '<div class="alert alert-success">User deleted successfully.</div>';
            echo '<script>setTimeout(function(){ window.location.href = "users.php"; }, 1500);</script>';
            exit;
        } else {
            echo '<div class="alert alert-danger">Error deleting user: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }
}
$rs = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
$user = $rs->num_rows ? $rs->fetch_assoc() : null;
?>
<?php if ($user): ?>
<div class="container py-4">
    <h2>User Details</h2>
    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to update this user?')">
        <table class="table table-bordered">
            <tbody>
                <!-- <tr><th>ID</th><td><?= htmlspecialchars($user['id']) ?></td></tr> -->
                <tr><th>Name</th><td><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></td></tr>
                <tr><th>Email</th><td><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></td></tr>
                <tr><th>Phone</th><td><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required></td></tr>
                <!-- <tr><th>Role</th><td><input type="text" name="role" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" required></td></tr> -->
                <!-- <tr><th>Verified</th><td><input type="checkbox" name="verified" <?= $user['verified'] == 1 ? 'checked' : '' ?>> Yes</td></tr> -->
                <!-- <tr><th>Date of Birth</th><td><input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user['dob'] ?? '') ?>"></td></tr> -->
                <tr><th>Address</th><td><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>"></td></tr>
                <tr><th>Occupation</th><td><input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>"></td></tr>
                <tr><th>ID Type</th><td><input type="text" name="id_type" class="form-control" value="<?= htmlspecialchars($user['id_type'] ?? '') ?>"></td></tr>
                <tr><th>ID Number</th><td><input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($user['id_number'] ?? '') ?>"></td></tr>
                <tr><th>ID Document</th><td>
                    <?php if (!empty($user['id_document'])): ?>
                        <a href="../uploads/<?= htmlspecialchars($user['id_document']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">View Document</a>
                    <?php else: ?>
                        <span class="text-muted">No document uploaded</span>
                    <?php endif; ?>
                </td></tr>
                <!-- <tr><th>Account Number</th><td><input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>"></td></tr> -->
                <!-- <tr><th>Bank Name</th><td><input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($user['bank_name'] ?? '') ?>"></td></tr> -->
                <!-- <tr><th>Account Name</th><td><input type="text" name="account_name" class="form-control" value="<?= htmlspecialchars($user['account_name'] ?? '') ?>"></td></tr> -->
                <tr class="table-secondary"><th colspan="2">Emergency Contact</th></tr>
                <tr><th>Name</th><td><input type="text" name="emgc_name" class="form-control" value="<?= htmlspecialchars($user['emgc_name'] ?? '') ?>"></td></tr>
                <tr><th>Address</th><td><input type="text" name="emgc_address" class="form-control" value="<?= htmlspecialchars($user['emgc_address'] ?? '') ?>"></td></tr>
                <tr><th>Phone</th><td><input type="text" name="emgc_phone" class="form-control" value="<?= htmlspecialchars($user['emgc_phone'] ?? '') ?>"></td></tr>
                <tr><th>Email</th><td><input type="email" name="emgc_email" class="form-control" value="<?= htmlspecialchars($user['emgc_email'] ?? '') ?>"></td></tr>
                <tr><th>Relationship</th><td><input type="text" name="emgc_relationship" class="form-control" value="<?= htmlspecialchars($user['emgc_relationship'] ?? '') ?>"></td></tr>
                <!-- <tr><th>Created At</th><td><?= htmlspecialchars($user['created_at']) ?></td></tr> -->
            </tbody>
        </table>
        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
    </form>
    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
        <button type="submit" name="delete_user" class="btn btn-danger mt-2">Delete User</button>
    </form>
</div>
<?php else: ?>
<div class="container py-4"><div class="alert alert-danger">User not found.</div></div>
<?php endif; ?>