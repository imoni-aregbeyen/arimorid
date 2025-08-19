<?php
// print_r($_SESSION);
// Array ( [user_id] => 7 [user_role] => owner [user_verified] => 0 [user_email] => olayemisoft@gmail.com [user_name] => Olayemi Israel [logged_in] => 1 )
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Service Apartments</li>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="breadcrumb-item">
                <a href="./?page=add-service-apartment" class="btn btn-sm btn-primary">
                    Add Service Apartment
                </a>
            </li>
        <?php endif; ?>
    </ol>
</nav>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        
        // Check if user has permission to delete (admin or owner of this apartment)
        $check_sql = "SELECT owner_id FROM service_apartments WHERE id = $delete_id";
        $check_result = $conn->query($check_sql);
        if ($check_result && $check_result->num_rows > 0) {
            $check_row = $check_result->fetch_assoc();
            if ($_SESSION['user_role'] === 'admin' || ($_SESSION['user_role'] === 'owner' && $check_row['owner_id'] == $_SESSION['user_id'])) {
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
            } else {
                echo "<div class='alert alert-danger'>You don't have permission to delete this apartment.</div>";
            }
        }
    }

    if (isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        
        // Check if user has permission to edit (admin or owner of this apartment)
        $check_sql = "SELECT owner_id FROM service_apartments WHERE id = $edit_id";
        $check_result = $conn->query($check_sql);
        if ($check_result && $check_result->num_rows > 0) {
            $check_row = $check_result->fetch_assoc();
            if ($_SESSION['user_role'] === 'admin' || ($_SESSION['user_role'] === 'owner' && $check_row['owner_id'] == $_SESSION['user_id'])) {
                $address = $conn->real_escape_string($_POST['address']);
                $title = $conn->real_escape_string($_POST['title']);
                $owner_daily_charge = floatval($_POST['owner_daily_charge']);
                $listing_daily_charge = floatval($_POST['listing_daily_charge']);
                $service_charge = floatval($_POST['service_charge']);
                $owner_id = ($_SESSION['user_role'] === 'admin') ? intval($_POST['owner_id']) : $_SESSION['user_id'];

                $edit_sql = "UPDATE service_apartments SET 
                                address = '$address', 
                                title = '$title', 
                                owner_daily_charge = $owner_daily_charge, 
                                listing_daily_charge = $listing_daily_charge, 
                                service_charge = $service_charge,
                                owner_id = $owner_id
                             WHERE id = $edit_id";
                if ($conn->query($edit_sql)) {
                    echo "<div class='alert alert-success'>Service apartment updated successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error updating service apartment: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>You don't have permission to edit this apartment.</div>";
            }
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
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <th>Owner</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Build the SQL query based on user role
                $sql = "SELECT id, owner_id, images, address, title, owner_daily_charge, listing_daily_charge, service_charge FROM service_apartments";
                if ($_SESSION['user_role'] !== 'admin') {
                    $sql .= " WHERE owner_id = " . intval($_SESSION['user_id']);
                }
                
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $images = explode(',', $row['images']);
                        $row['images'] = $images[0]; // Display only the first image

                        // Fetch owners for the dropdown (only for admin)
                        $owners_options = '';
                        if ($_SESSION['user_role'] === 'admin') {
                            $owners_sql = "SELECT id, name FROM users WHERE role='owner'";
                            $owners_result = $conn->query($owners_sql);
                            if ($owners_result->num_rows > 0) {
                                while ($owner = $owners_result->fetch_assoc()) {
                                    $selected = $owner['id'] == $row['owner_id'] ? 'selected' : '';
                                    $owners_options .= "<option value='{$owner['id']}' $selected>{$owner['name']}</option>";
                                }
                            } else {
                                $owners_options = "<option value=''>No owners available</option>";
                            }
                        }

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
                                        <input type='number' step='0.01' name='service_charge' value='{$row['service_charge']}' class='form-control form-control-sm'>";
                                        
                        if ($_SESSION['user_role'] === 'admin') {
                            echo "</td>
                                <td>
                                    <select name='owner_id' class='form-control form-control-sm'>
                                        $owners_options
                                    </select>
                                </td>";
                        } else {
                            echo "</td>";
                        }
                        
                        echo "<td>
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
                    $colspan = $_SESSION['user_role'] === 'admin' ? '9' : '8';
                    echo "<tr><td colspan='$colspan' class='text-center'>No service apartments found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>