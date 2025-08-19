<div class="container py-5">
  <form action="./ac/action-forgot-password.php" method="post" style="max-width: 400px; margin: auto;" class="shadow p-3">
    <h2 class="text-center mb-3">Forgot Password</h2>
    <div class="mb-3">
      <label for="email" class="form-label">Enter your email address</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3 d-grid gap-2">
      <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </div>
    <div class="text-center">
      <a href="./?page=login">Back to Login</a>
    </div>
  </form>
</div>
