<?php
$id = test_input($_GET['id']) ?? null;

$sql = "SELECT * FROM properties WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$images = json_decode($property['images']);
$stmt->close();
if (!$property) {
    header("Location: ?page=properties&error=Property not found");
    exit;
}
$sql = "DELETE FROM properties WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
if ($images) {
    foreach ($images as $image) {
      unlink("../uploads/properties/$image");
    }
}
?>

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Delete Property</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?page=properties">Properties</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header">
              <!-- <h5>Hello card</h5> -->
            </div>
            <div class="card-body">
              <div class="alert alert-success" role="alert">
                Property deleted successfully.
              </div>
              <a href="?page=properties" class="btn btn-primary">Go back to Properties</a>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>