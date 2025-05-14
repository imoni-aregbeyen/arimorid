<?php
try {
    $sql = "SELECT * FROM users WHERE role='owner' ORDER BY created_at DESC LIMIT 12";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $owners[] = $row;
        }
    } else {
        $owners = [];
    }
} catch (mysqli_sql_exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Check for duplicate entry
    $checkSql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "An owner with this email already exists.";
    } else {
        // Insert new owner
        $insertSql = "INSERT INTO users (name, email, phone, role) VALUES (?, ?, ?, 'owner')";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sss", $name, $email, $phone);
        if ($stmt->execute()) {
            $success = "New owner added successfully.";
            echo "<script>setTimeout(function(){ window.location.href = '?page=owners'; }, 2000);</script>";
        } else {
            $error = "Failed to add owner.";
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
                <h5 class="m-b-10">Owners</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <!-- <li class="breadcrumb-item"><a href="?page=add-property" class="">Add Property</a></li> -->
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <h5>Owners</h5>
              <button class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#addOwnerModal">New Owner</button>
            </div>
            <div class="card-body">
              <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?= $error ?>
                  <button type="button" class="close btn btn-outline-dark" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <?= $success ?>
                  <button type="button" class="close btn btn-outline-dark" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              <table class="table">
                <thead>
                  <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $sn = 1; foreach ($owners as $owner): ?>
                    <tr>
                      <td><?= $sn++ ?></td>
                      <td><?= $owner['name'] ?></td>
                      <td><?= $owner['email'] ?></td>
                      <td><?= $owner['phone'] ?></td>
                      <td>
                        <a href="?page=edit-owner&id=<?= $owner['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?page=delete-owner&id=<?= $owner['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->

      <!-- Add Owner Modal -->
      <div class="modal fade" id="addOwnerModal" tabindex="-1" role="dialog" aria-labelledby="addOwnerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addOwnerModalLabel">Add New Owner</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                <div class="form-group">
                  <label for="ownerName">Name</label>
                  <input type="text" class="form-control" id="ownerName" name="name" required>
                </div>
                <div class="form-group">
                  <label for="ownerEmail">Email</label>
                  <input type="email" class="form-control" id="ownerEmail" name="email" required>
                </div>
                <div class="form-group">
                  <label for="ownerPhone">Phone</label>
                  <input type="text" class="form-control" id="ownerPhone" name="phone" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- End Add Owner Modal -->

      <!-- Include Bootstrap and jQuery -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>