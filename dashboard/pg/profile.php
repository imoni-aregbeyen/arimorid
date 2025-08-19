<!-- // profile page
// print_r($_SESSION);
// Array ( [user_id] => 1 [user_role] => admin [user_verified] => 0 [user_email] => admin@arimoridgr.com.ng [user_name] => Administrator [logged_in] => 1 )
// users(table): id, name, email, password, role, verified, phone, account_number, bank_name, account_name, created_at -->
<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../?page=login');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$success = $error = '';


// Handle profile update and password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : null;
    $bank_name = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : null;
    $account_name = isset($_POST['account_name']) ? trim($_POST['account_name']) : null;

    // Password update logic
    $password_updated = false;
    if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $error = 'All password fields are required to change password.';
        } else if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'New password and confirm password do not match.';
        } else {
            // Fetch current password hash
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && password_verify($_POST['current_password'], $row['password'])) {
                $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $new_password_hash, $user_id);
                if ($stmt->execute()) {
                    $password_updated = true;
                } else {
                    $error = 'Failed to update password.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }

    if (!$error) {
        $query = "UPDATE users SET name=?, email=?, phone=?";
        $params = [$name, $email, $phone];
        $types = 'sss';
        if ($user_role === 'owner') {
            $query .= ", account_number=?, bank_name=?, account_name=?";
            $params = array_merge($params, [$account_number, $bank_name, $account_name]);
            $types .= 'sss';
        }
        $query .= " WHERE id=?";
        $params[] = $user_id;
        $types .= 'i';

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            if ($password_updated) {
                $success = 'Profile and password updated successfully!';
            } else {
                $success = 'Profile updated successfully!';
            }
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

?>
<div class="container mt-4">
    <h2>My Profile</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
        </div>
        <?php if ($user_role === 'owner'): ?>
        <div class="col-md-6">
            <label class="form-label">Account Number</label>
            <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($user['account_number']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($user['bank_name']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Account Name</label>
            <input type="text" name="account_name" class="form-control" value="<?= htmlspecialchars($user['account_name']) ?>">
        </div>
        <?php endif; ?>
        <hr class="my-4">
        <h5>Change Password</h5>
        <div class="col-md-4">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-4">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" autocomplete="off">
        </div>
        <div class="col-12">
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>