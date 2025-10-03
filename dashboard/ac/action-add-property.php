<?php
// Include database connection & test_input function
require_once '../../config/db.php';

// Ensure the properties table exists
$tableQuery = "
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_type VARCHAR(255) NOT NULL,
    for_sell_rent VARCHAR(50) NOT NULL,
    owner_price DECIMAL(10, 2),
    listing_price DECIMAL(10, 2) NOT NULL,
    title VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    sqft INT,
    bed INT,
    bath INT,
    images JSON NOT NULL,
    owner_id INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($tableQuery)) {
    die('Error creating table: ' . $conn->error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $propertyType = test_input($_POST['propertyType']);
    $forSellRent = test_input($_POST['forSellRent']);
    $listingPrice = test_input($_POST['listingPrice']);
    $title = test_input($_POST['title']);
    $address = test_input($_POST['address']);
    $sqft = isset($_POST['sqft']) ? test_input($_POST['sqft']) : null;

    // Validate required fields
    if (empty($propertyType) || empty($forSellRent) || empty($listingPrice) || empty($title) || empty($address)) {
        die('Required fields are missing.');
    }

    // Handle file uploads
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../../uploads/properties/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $uploadedImages[] = $fileName;
            } else {
                die('Failed to upload image: ' . $fileName);
            }
        }
    }

    // Convert uploaded images array to JSON
    $imagesJson = json_encode($uploadedImages);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO properties (property_type, for_sell_rent, listing_price, title, address, sqft, images, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssdssds", $propertyType, $forSellRent, $listingPrice, $title, $address, $sqft, $imagesJson);

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
