<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Service Apartments</li>
        <li class="breadcrumb-item">
            <a href="./?page=add-service-apartment" class="btn btn-sm btn-primary">
                Add Service Apartment
            </a>
        </li>
    </ol>
</nav>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        
        // Fetch all image file names before deleting the record
        $image_sql = "SELECT images FROM service_apartments WHERE id = $delete_id";
        $image_result = $conn->query($image_sql);
        if ($image_result && $image_result->num_rows > 0) {
            $image_row = $image_result->fetch_assoc();
            $images = explode(',', $image_row['images']);
            foreach ($images as $image) {
                $image_path = "../uploads/" . $image;
                if (file_exists($image_path)) {
                    unlink($image_path); // Delete each image file
                }
            }
        }

        $delete_sql = "DELETE FROM service_apartments WHERE id = $delete_id";
        if ($conn->query($delete_sql)) {
            echo "<div class='alert alert-success'>Service apartment deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting service apartment: " . $conn->error . "</div>";
        }
    }

    if (isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        $address = $conn->real_escape_string($_POST['address']);
        $title = $conn->real_escape_string($_POST['title']);
        $owner_daily_charge = floatval($_POST['owner_daily_charge']);
        $listing_daily_charge = floatval($_POST['listing_daily_charge']);
        $service_charge = floatval($_POST['service_charge']);

        $edit_sql = "UPDATE service_apartments SET 
                        address = '$address', 
                        title = '$title', 
                        owner_daily_charge = $owner_daily_charge, 
                        listing_daily_charge = $listing_daily_charge, 
                        service_charge = $service_charge 
                     WHERE id = $edit_id";
        if ($conn->query($edit_sql)) {
            echo "<div class='alert alert-success'>Service apartment updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating service apartment: " . $conn->error . "</div>";
        }
    }
}
?>

<div class="card mt-4">
    <div class="card-header">
        <h5>Service Apartments</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Address</th>
                    <th>Title</th>
                    <th>Owner Daily Charge</th>
                    <th>Listing Daily Charge</th>
                    <th>Service Charge</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id, images, address, title, owner_daily_charge, listing_daily_charge, service_charge FROM service_apartments";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { $images = explode(',', $row['images']);
                        $row['images'] = $images[0]; // Display only the first image
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td><img src='../uploads/{$row['images']}' alt='Apartment Image' style='width: 100px; height: auto;'></td>
                                <td>
                                    <form method='POST' class='d-inline'>
                                        <input type='hidden' name='edit_id' value='{$row['id']}'>
                                        <input type='text' name='address' value='{$row['address']}' class='form-control form-control-sm'>
                                </td>
                                <td>
                                        <input type='text' name='title' value='{$row['title']}' class='form-control form-control-sm'>
                                </td>
                                <td>
                                        <input type='number' step='0.01' name='owner_daily_charge' value='{$row['owner_daily_charge']}' class='form-control form-control-sm'>
                                </td>
                                <td>
                                        <input type='number' step='0.01' name='listing_daily_charge' value='{$row['listing_daily_charge']}' class='form-control form-control-sm'>
                                </td>
                                <td>
                                        <input type='number' step='0.01' name='service_charge' value='{$row['service_charge']}' class='form-control form-control-sm'>
                                </td>
                                <td>
                                        <button type='submit' class='btn btn-sm btn-warning' style='width: 80px;'>Update</button>
                                    </form>
                                    <form method='POST' class='d-inline' onsubmit='return confirm(\"Are you sure you want to delete this entry?\")'>
                                        <input type='hidden' name='delete_id' value='{$row['id']}'>
                                        <button type='submit' class='btn btn-sm btn-danger' style='width: 80px;'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No service apartments found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
