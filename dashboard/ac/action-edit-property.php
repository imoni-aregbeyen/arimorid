<?php
// Include database connection & test_input function
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = test_input($_POST['id']);
    $sql = "SELECT * FROM properties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    if (!$property) {
        die('Property not found.');
    }
    $images = json_decode($property['images'], true);
    $propertyType = test_input($_POST['propertyType']);
    $forSellRent = test_input($_POST['forSellRent']);
    $listingPrice = test_input($_POST['listingPrice']);
    $title = test_input($_POST['title']);
    $address = test_input($_POST['address']);
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../../uploads/properties/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $images[] = $fileName;
                $sql = "UPDATE properties SET images = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $imagesJson = json_encode($images);
                $stmt->bind_param("si", $imagesJson, $id);
                if (!$stmt->execute()) {
                    die('Error updating images: ' . $stmt->error);
                }
            } else {
                die('Failed to upload image: ' . $fileName);
            }
        }
    }
    $sql = "UPDATE properties SET property_type = ?, for_sell_rent = ?, listing_price = ?, title = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssi", $propertyType, $forSellRent, $listingPrice, $title, $address, $id);
    if ($stmt->execute()) {
        header('Location: ../?page=properties&status=success');
        exit();
    } else {
        die('Error: ' . $stmt->error);
    }
} else {
    die('Invalid request method.');
}
?>
