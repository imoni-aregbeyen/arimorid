<?php
require_once '../config/db.php';
$token = $_GET['token'] ?? '';
$valid = false;
if ($token) {
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $valid = true;
        $user = $result->fetch_assoc();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $password, $user['user_id']);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    echo '<div class="alert alert-success">Password reset successful. <a href=\"./?page=login\">Login</a></div>';
    exit;
}
?>
<div class="container py-5">
  <form action="" method="post" style="max-width: 400px; margin: auto;" class="shadow p-3">
    <h2 class="text-center mb-3">Reset Password</h2>
    <?php if ($valid): ?>
      <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <div class="mb-3 d-grid gap-2">
        <button type="submit" class="btn btn-primary">Reset Password</button>
      </div>
    <?php else: ?>
      <div class="alert alert-danger">Invalid or expired reset link.</div>
    <?php endif; ?>
  </form>
</div>
