<div class="container py-5">
  <form action="./ac/action-login.php" method="post" style=
  "max-width: 400px; margin: auto;" class="shadow p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="text-center">Login</h2>
      <a href="./?page=register" class="">Don't have an account?</a>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <div class="mb-3 d-grid gap-2">
      <button type="submit" class="btn btn-primary">Login</button>
    </div>
  </form>
</div>