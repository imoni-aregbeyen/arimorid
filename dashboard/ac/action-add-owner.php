<?php
require_once '../../config/db.php'; // Include database connection and configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = test_input($_POST['property_id']);
    $owner_id = test_input($_POST['owner']);

    if ($owner_id === 'new') {
        // Add new owner
        $name = test_input($_POST['name']);
        $phone = test_input($_POST['phone']);
        $password = password_hash($phone, PASSWORD_BCRYPT); // Hash the password
        $email = test_input($_POST['email']);
        $account_number = test_input($_POST['account_number']);
        $bank_name = test_input($_POST['bank_name']);
        $account_name = test_input($_POST['account_name']);

        $stmt = $conn->prepare("INSERT INTO users (name, password phone, email, account_number, bank_name, account_name, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'owner')");
        $stmt->bind_param("sssssss", $name, $password, $phone, $email, $account_number, $bank_name, $account_name);

        if ($stmt->execute()) {
            $owner_id = $conn->insert_id; // Get the ID of the newly created owner
        } else {
            echo "<script>alert('Failed to add new owner.'); window.history.back();</script>";
            exit;
        }
    }

    // Assign owner to the property
    $stmt = $conn->prepare("UPDATE properties SET owner_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $owner_id, $property_id);

    if ($stmt->execute()) {
        echo "<script>alert('Owner added successfully!'); window.location.href='../?page=properties';</script>";
    } else {
        echo "<script>alert('Failed to assign owner to property.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
?>
