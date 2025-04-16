<?php
// Include database connection & test_input function
require_once '../../config/db.php';

$id = test_input($_GET['id']);
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
if (!$images) {
    die('No images found for this property.');
}
$index = test_input($_GET['index']);
if (!isset($images[$index])) {
    die('Image not found.');
}
$uploadDir = '../../uploads/properties/';
$filePath = $uploadDir . $images[$index];
if (file_exists($filePath)) {
    unlink($filePath);
} else {
    die('File not found.');
}
array_splice($images, $index, 1);
$sql = "UPDATE properties SET images = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", json_encode($images), $id);
if ($stmt->execute()) {
    header('Location: ../?page=edit-property&id=' . $id . '&status=success');
} else {
    die('Error updating images: ' . $stmt->error);
}