<?php
$email = isset($_SESSION['temp_user']) ? $_SESSION['temp_user']['email'] : '';
$password = '';
if (isset($_SESSION['temp_user'])) {  // Clear temporary user session data if any
  unset($_SESSION['temp_user']);
}
?>
<div class="container py-5">
  <form action="./ac/action-login.php" method="post" style=
  "max-width: 400px; margin: auto;" class="shadow p-3">
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="text-center">Login</h2>
      <a href="./?page=register" class="">Don't have an account?</a>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" name="email" id="email" value="<?= $email ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" id="password" value="<?= $password ?>" class="form-control" required>
      <div class="form-check mt-2">
        <input type="checkbox" class="form-check-input" id="togglePassword">
        <label class="form-check-label" for="togglePassword">Show Password</label>
      </div>
    </div>
    <div class="mb-2 text-end">
      <a href="./?page=forgot-password">Forgot Password?</a>
    </div>
    <div class="mb-3 d-grid gap-2">
      <button type="submit" class="btn btn-primary">Login</button>
    </div>
  </form>
</div>
<?php
unset($_SESSION['temp_user']); // Clear temporary user session data if any
?>
<script>
  document.getElementById('togglePassword').addEventListener('change', function () {
    const passwordField = document.getElementById('password');
    passwordField.type = this.checked ? 'text' : 'password';
  });
</script>