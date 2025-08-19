<?php
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires_at=?");
        $stmt->bind_param("issss", $user['id'], $token, $expires, $token, $expires);
        $stmt->execute();
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/../pg/reset-password.php?token=' . $token;
        // TODO: Send $resetLink to user's email
        echo '<div class="alert alert-success">A password reset link has been sent to your email address.</div>';
    } else {
        echo '<div class="alert alert-danger">Email address not found.</div>';
    }
}
?>
