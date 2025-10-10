<div class="container py-5">
  <form action="./ac/action-register.php" method="post" style=
  "max-width: 400px; margin: auto;" class="shadow p-3">
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="text-center">Sign up</h2>
      <a href="./?page=login" class="">Already have an account?</a>
    </div>
    <div class="mb-3">
      <label for="name" class="form-label">Full Name</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <div class="input-group">
        <input type="password" name="password" id="password" class="form-control" required>
        <button class="btn btn-outline-primary" type="button" id="togglePassword" tabindex="-1">
          <span id="togglePasswordIcon" class="fa fa-eye"></span>
        </button>
      </div>
    </div>
    <div class="mb-2">
      <small class="text-muted">By signing up, you agree to our <a href="./?page=privacy_policy" target="_blank">Privacy Policy</a>.</small>
    </div>
    <div class="mb-3 d-grid gap-2">
      <button type="submit" class="btn btn-primary">Create Account</button>
    </div>
  </form>
</div>
<script>
  document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordField.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
</script>