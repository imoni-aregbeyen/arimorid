<?php
// Include database connection & test_input function
require_once '../../config/db.php';

// Ensure the properties table exists
$tableQuery = "
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_type VARCHAR(255) NOT NULL,
    for_sell_rent VARCHAR(50) NOT NULL,
    owner_price DECIMAL(10, 2) NOT NULL,
    listing_price DECIMAL(10, 2) NOT NULL,
    title VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    sqft INT NOT NULL,
    bed INT NOT NULL,
    bath INT NOT NULL,
    images JSON NOT NULL,
    owner_id INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE SET DEFAULT
)";
if (!$conn->query($tableQuery)) {
    die('Error creating table: ' . $conn->error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $propertyType = test_input($_POST['propertyType']);
    $forSellRent = test_input($_POST['forSellRent']);
    $ownerPrice = test_input($_POST['ownerPrice']);
    $listingPrice = test_input($_POST['listingPrice']);
    $title = test_input($_POST['title']);
    $address = test_input($_POST['address']);
    $sqft = test_input($_POST['sqft']);
    $bed = test_input($_POST['bed']);
    $bath = test_input($_POST['bath']);

    // Validate required fields
    if (empty($propertyType) || empty($forSellRent) || empty($ownerPrice) || empty($listingPrice) || empty($title) || empty($address) || empty($sqft) || empty($bed) || empty($bath)) {
        die('All fields are required.');
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
    $stmt = $conn->prepare("INSERT INTO properties (property_type, for_sell_rent, owner_price, listing_price, title, address, sqft, bed, bath, images, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssddsssdds", $propertyType, $forSellRent, $ownerPrice, $listingPrice, $title, $address, $sqft, $bed, $bath, $imagesJson);

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
