<?php

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../?page=login');
    exit;
}
$user_id = $_SESSION['user_id'];

$success = $error = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle KYC Information Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kyc_info'])) {
    $id_type = trim($_POST['id_type'] ?? '');
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
        $target = '../../uploads/' . $filename;
        if (move_uploaded_file($_FILES['id_document']['tmp_name'], $target)) {
            $id_document = $filename;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET id_type=?, id_document=?, emgc_name=?, emgc_address=?, emgc_phone=?, emgc_email=?, emgc_relationship=?, verified=1 WHERE id=?");
    $stmt->bind_param("sssssssi", $id_type, $id_document, $emgc_name, $emgc_address, $emgc_phone, $emgc_email, $emgc_relationship, $user_id);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Update</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>KYC Information</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
        <input type="hidden" name="update_kyc_info" value="1">
        <div class="col-md-4">
            <label class="form-label">ID Type</label>
            <select name="id_type" class="form-select">
                <option value="NIN" <?= ($user['id_type'] ?? '') == 'NIN' ? 'selected' : '' ?>>NIN</option>
                <option value="Passport" <?= ($user['id_type'] ?? '') == 'Passport' ? 'selected' : '' ?>>Passport</option>
                <option value="Driver's License" <?= ($user['id_type'] ?? '') == "Driver's License" ? 'selected' : '' ?>>Driver's License</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">ID Document</label>
            <input type="file" name="id_document" class="form-control" accept="image/*,application/pdf">
            <?php if (!empty($user['id_document'])): ?>
                <small class="text-muted">Current: <a href="../../uploads/<?= htmlspecialchars($user['id_document']) ?>" target="_blank">View Document</a></small>
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
</div>
</body>
</html>
