<?php
require_once '../config/db.php'; // Include database connection

// Fetch owner details
if (isset($_GET['id'])) {
    $ownerId = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'owner'");
        $stmt->bind_param("i", $ownerId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $owner = $result->fetch_assoc();
        } else {
            die("Owner not found.");
        }
    } catch (mysqli_sql_exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? AND role = 'owner'");
        $stmt->bind_param("sssi", $name, $email, $phone, $ownerId);
        if ($stmt->execute()) {
            header("Location: ./?page=owners");
            exit;
        } else {
            $error = "Failed to update owner.";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Edit Owner</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?page=owners">Owners</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <div class="col-12">
          <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
          <?php endif; ?>
          <div class="card">
            <div class="card-body">
              <form action="" method="post">
                <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($owner['name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($owner['email']) ?>" required>
                </div>
                <div class="mb-3">
                  <label for="phone" class="form-label">Phone</label>
                  <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($owner['phone']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Owner</button>
                <a href="?page=owners" class="btn btn-secondary">Cancel</a>
              </form>
            </div>
          </div>
        </div>
      </div>