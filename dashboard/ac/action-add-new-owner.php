<?php
require_once '../../config/db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = test_input($_POST['name']);
    $phone = test_input($_POST['phone']);
    $email = test_input($_POST['email']);
    $account_number = test_input($_POST['account_number']);
    $bank_name = test_input($_POST['bank_name']);
    $account_name = test_input($_POST['account_name']);
    $property_id = test_input($_POST['property_id']);
    $password = password_hash(test_input($_POST['phone']), PASSWORD_BCRYPT);
    $role = 'owner';

    // Insert new owner into the owners table
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, account_number, bank_name, account_name, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $phone, $email, $password, $account_number, $bank_name, $account_name, $role);

    if ($stmt->execute()) {
        $owner_id = $stmt->insert_id; // Get the ID of the newly added owner

        // Update the property with the new owner ID
        $update_stmt = $conn->prepare("UPDATE properties SET owner_id = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $owner_id, $property_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Owner added successfully and property updated!'); window.location.href='../?page=properties';</script>";
        } else {
            echo "<script>alert('Failed to update property with owner ID.'); window.history.back();</script>";
        }
        $update_stmt->close();
    } else {
        echo "<script>alert('Failed to add owner.'); window.history.back();</script>";
    }
    $stmt->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}

$conn->close();
?>
