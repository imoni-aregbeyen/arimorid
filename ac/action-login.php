<?php
session_start();
require_once '../config/db.php'; // Include database connection, test_input function, and other necessary files

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Prepare SQL query to fetch user
        $stmt = $conn->prepare("SELECT id, name, password, role, verified, phone, dob, address, id_type, id_document FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_verified'] = $user['verified'];
                $_SESSION['user_email'] = $email; // Store email in session
                $_SESSION['user_name'] = $user['name']; // Store name in session
                $_SESSION['user_phone'] = $user['phone']; // Store phone in session
                $_SESSION['user_dob'] = $user['dob']; // Store date of birth
                $_SESSION['user_address'] = $user['address']; // Store address in session
                $_SESSION['user_id_type'] = $user['id_type']; // Store ID type
                $_SESSION['user_id_document'] = $user['id_document']; // Store ID document in session
                $_SESSION['logged_in'] = true;
                // Redirect based on role and verification status
                if ($user['role'] === 'admin' || $user['role'] === 'owner') {
                    header("Location: ../dashboard/");
                } elseif ($user['role'] === 'user') {
                    if ($user['verified'] === 0) {
                        header("Location: ../dashboard/?page=kyc");
                    } else {
                        header("Location: ../?page=property-list");
                    }
                }
                exit;
            } else {
                $_SESSION['error'] = "Invalid email or password.";
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}
die(header('location: ' . $_SERVER['HTTP_REFERER'])); // Redirect back to the login page
?>
