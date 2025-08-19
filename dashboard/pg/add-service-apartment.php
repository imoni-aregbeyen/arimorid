<?php
$sql = "SELECT * FROM users WHERE role = 'owner'"; // id, name, email, phone
$result = $conn->query($sql);
if ($result === false) {
    echo "<script>alert('Database error: Failed to fetch owners');</script>";
    exit;
}
$owners = [];
while ($row = $result->fetch_assoc()) {
    $owners[] = $row;
}
if (empty($owners)) {
    echo "<script>alert('No owners found. Please add an owner first.'); window.location.href='./?page=owners';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $address = $_POST['address'];
    $title = $_POST['title'];
    $owner_daily_charge = $_POST['owner_daily_charge'];
    $listing_daily_charge = $_POST['listing_daily_charge'];
    $service_charge = $_POST['service_charge'];
    $owner_id = $_POST['owner_id'];
    $images = [];

    // Handle file upload
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            if (!move_uploaded_file($file_tmp, "../uploads/" . $file_name)) {
                echo "<script>alert('Failed to upload file: $file_name');</script>";
                exit;
            } else {
                $images[] = $file_name;
            }
        }
        $images = implode(',', $images);
    }

    $sql = "INSERT INTO service_apartments (images, address, title, owner_daily_charge, listing_daily_charge, service_charge, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "<script>alert('Database error: Failed to prepare statement');</script>";
        exit;
    }
    $stmt->bind_param("sssdddi",$images, $address, $title, $owner_daily_charge, $listing_daily_charge, $service_charge, $owner_id);
    if (!$stmt->execute()) {
        echo "<script>alert('Database error: Failed to execute statement');</script>";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    $conn->close();
    // Redirect to the service apartments page
    echo "<script>alert('Service Apartment added successfully!'); window.location.href='./?page=service-apartments';</script>";
}
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Service Apartment</li>
    </ol>
</nav>

<div class="card mt-4">
    <div class="card-header">
        <h5>Add Service Apartment</h5>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="apartmentImages" class="form-label">Images</label>
                <input type="file" class="form-control" id="apartmentImages" name="images[]" multiple>
            </div>
            <div class="mb-3">
                <label for="apartmentAddress" class="form-label">Address</label>
                <input type="text" class="form-control" id="apartmentAddress" name="address" placeholder="Enter address">
            </div>
            <div class="mb-3">
                <label for="apartmentTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="apartmentTitle" name="title" placeholder="Enter title">
            </div>
            <div class="mb-3">
                <label for="ownerDailyCharge" class="form-label">Owner Daily Charge</label>
                <input type="number" class="form-control" id="ownerDailyCharge" name="owner_daily_charge" placeholder="Enter owner daily charge">
            </div>
            <div class="mb-3">
                <label for="listingDailyCharge" class="form-label">Listing Daily Charge</label>
                <input type="number" class="form-control" id="listingDailyCharge" name="listing_daily_charge" placeholder="Enter listing daily charge">
            </div>
            <div class="mb-3">
                <label for="serviceCharge" class="form-label">Caution Fee</label>
                <input type="number" class="form-control" id="serviceCharge" name="service_charge" placeholder="Enter caution fee">
            </div>
            <div class="mb-3">
                <label for="owner_id" class="form-label">Select Owner</label>
                <select class="form-select" id="owner_id" name="owner_id">
                    <option value="" disabled selected>Select an owner</option>
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?= $owner['id'] ?>"><?= $owner['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- VAT 7.5% of listing daily charge -->
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>