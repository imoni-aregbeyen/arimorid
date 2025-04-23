<?php
require_once '../config/db.php'; // Include database connection, test_input function, and other necessary files

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = test_input($_POST['name']);
    $email = test_input($_POST['email']);
    $password = password_hash(test_input($_POST['password']), PASSWORD_BCRYPT);

    try {
        // Ensure the users table exists
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user', 'owner') NOT NULL DEFAULT 'user',
                verified TINYINT(1) DEFAULT 0,
                phone VARCHAR(20) DEFAULT NULL,
                account_number VARCHAR(50) DEFAULT NULL,
                bank_name VARCHAR(255) DEFAULT NULL,
                account_name VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        if (!$conn->query($createTableQuery)) {
            throw new Exception("Table creation failed: " . $conn->error);
        }

        // Check if the email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($emailCount);
        $stmt->fetch();
        $stmt->close();

        if ($emailCount > 0) {
            die('Email already registered.');
        }

        // Check if this is the first user
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $stmt->bind_result($userCount);
        $stmt->fetch();
        $stmt->close();
        $isFirstUser = $userCount == 0;

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $role = $isFirstUser ? 'admin' : 'user';
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        $stmt->close();

        // Redirect to login page
        header('Location: ../?page=login');
        exit;
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
} else {
    die('Invalid request method.');
}
?>
