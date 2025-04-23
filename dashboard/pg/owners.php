<?php
try {
    $sql = "SELECT * FROM owners ORDER BY created_at DESC LIMIT 12";
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
            <div class="card-header">
              <h5>Hello card</h5>
            </div>
            <div class="card-body">
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