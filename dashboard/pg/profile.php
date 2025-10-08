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

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile updates based on which section was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Basic Information Update
    if (isset($_POST['update_basic_info'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            if ($stmt->execute()) {
                $success = 'Basic information updated successfully.';
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = 'Error updating basic information.';
            }
        }
    }

    // Handle KYC Information Update
    elseif (isset($_POST['update_kyc_info'])) {
        $id_type = trim($_POST['id_type'] ?? '');
        $id_number = trim($_POST['id_number'] ?? '');
        $emgc_name = trim($_POST['emgc_name'] ?? '');
        $emgc_address = trim($_POST['emgc_address'] ?? '');
        $emgc_phone = trim($_POST['emgc_phone'] ?? '');
        $emgc_email = trim($_POST['emgc_email'] ?? '');
        $emgc_relationship = trim($_POST['emgc_relationship'] ?? '');

        // Handle ID document upload
        $id_document = $user['id_document'] ?? '';
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION);
            $filename = 'id_doc_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $target = '../uploads/' . $filename;
            if (move_uploaded_file($_FILES['id_document']['tmp_name'], $target)) {
                $id_document = $filename;
            }
        }

    $stmt = $conn->prepare("UPDATE users SET id_type=?, id_number=?, id_document=?, emgc_name=?, emgc_address=?, emgc_phone=?, emgc_email=?, emgc_relationship=?, verified=1 WHERE id=?");
    $stmt->bind_param("ssssssssi", $id_type, $id_number, $id_document, $emgc_name, $emgc_address, $emgc_phone, $emgc_email, $emgc_relationship, $user_id);
        if ($stmt->execute()) {
            $success = 'KYC information updated successfully.';
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Error updating KYC information.';
        }
    }

    // Handle Bank Information Update (Owners only)
    elseif (isset($_POST['update_bank_info']) && $user_role === 'owner') {
        $account_number = trim($_POST['account_number'] ?? '');
        $bank_name = trim($_POST['bank_name'] ?? '');
        $account_name = trim($_POST['account_name'] ?? '');

        $stmt = $conn->prepare("UPDATE users SET account_number=?, bank_name=?, account_name=? WHERE id=?");
        $stmt->bind_param("sssi", $account_number, $bank_name, $account_name, $user_id);
        if ($stmt->execute()) {
            $success = 'Bank information updated successfully.';
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Error updating bank information.';
        }
    }

    // Handle Password Change
    elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required.';
        } else if ($new_password !== $confirm_password) {
            $error = 'New password and confirm password do not match.';
        } else {
            // Fetch current password hash
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && password_verify($current_password, $row['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $new_password_hash, $user_id);
                if ($stmt->execute()) {
                    $success = 'Password updated successfully.';
                } else {
                    $error = 'Error updating password.';
                }
                $stmt->close();
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>

<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-10">Profile</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                    <li class="breadcrumb-item">Profile</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Basic Information Form -->
                <form method="POST" class="row g-3 mb-4">
                    <input type="hidden" name="update_basic_info" value="1">
                    <h5>Basic Information</h5>
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Basic Information</button>
                    </div>
                </form>

                <hr class="my-4">

                <!-- KYC Information Form -->
                <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
                    <input type="hidden" name="update_kyc_info" value="1">
                    <h5>KYC Information</h5>
                    <div class="col-md-4">
                        <label class="form-label">ID Type</label>
                        <select name="id_type" class="form-select">
                            <option value="NIN" <?= ($user['id_type'] ?? '') == 'NIN' ? 'selected' : '' ?>>NIN</option>
                            <option value="Passport" <?= ($user['id_type'] ?? '') == 'Passport' ? 'selected' : '' ?>>Passport</option>
                            <option value="Driver's License" <?= ($user['id_type'] ?? '') == "Driver's License" ? 'selected' : '' ?>>Driver's License</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Number</label>
                        <input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($user['id_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Document</label>
                        <input type="file" name="id_document" class="form-control" accept="image/*,application/pdf">
                        <?php if (!empty($user['id_document'])): ?>
                            <small class="text-muted">Current: <a href="../uploads/<?= htmlspecialchars($user['id_document']) ?>" target="_blank">View Document</a></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emgc_name" class="form-control" value="<?= htmlspecialchars($user['emgc_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Address</label>
                        <input type="text" name="emgc_address" class="form-control" value="<?= htmlspecialchars($user['emgc_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emgc_phone" class="form-control" value="<?= htmlspecialchars($user['emgc_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Emergency Contact Email</label>
                        <input type="email" name="emgc_email" class="form-control" value="<?= htmlspecialchars($user['emgc_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Emergency Contact Relationship</label>
                        <select name="emgc_relationship" class="form-select">
                            <option value="Parent" <?= ($user['emgc_relationship'] ?? '') == 'Parent' ? 'selected' : '' ?>>Parent</option>
                            <option value="Sibling" <?= ($user['emgc_relationship'] ?? '') == 'Sibling' ? 'selected' : '' ?>>Sibling</option>
                            <option value="Spouse" <?= ($user['emgc_relationship'] ?? '') == 'Spouse' ? 'selected' : '' ?>>Spouse</option>
                            <option value="Friend" <?= ($user['emgc_relationship'] ?? '') == 'Friend' ? 'selected' : '' ?>>Friend</option>
                            <option value="Other" <?= ($user['emgc_relationship'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update KYC Information</button>
                    </div>
                </form>

                <?php if ($user_role === 'owner'): ?>
                <hr class="my-4">

                <!-- Bank Information Form -->
                <form method="POST" class="row g-3 mb-4">
                    <input type="hidden" name="update_bank_info" value="1">
                    <h5>Bank Account Information</h5>
                    <div class="col-md-6">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($user['account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($user['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" class="form-control" value="<?= htmlspecialchars($user['account_name'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Bank Information</button>
                    </div>
                </form>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Password Change Form -->
                <form method="POST" class="row g-3">
                    <input type="hidden" name="update_password" value="1">
                    <h5>Change Password</h5>
                    <div class="col-md-4">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>