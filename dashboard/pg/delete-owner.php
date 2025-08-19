<?php
// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "You don't have permission to perform this action";
    header('Location: ?page=owners');
    exit;
}

$id = (int)$_GET['id'] ?? 0;

if ($id > 0) {
    try {
        // Check if owner has any apartments before deleting
        $check_apartments = $conn->query("SELECT COUNT(*) FROM service_apartments WHERE owner_id = $id");
        $apartment_count = $check_apartments->fetch_row()[0];
        
        if ($apartment_count > 0) {
            $_SESSION['error_message'] = "Cannot delete owner - they still have apartments listed. Please reassign or delete the apartments first.";
        } else {
            // Delete owner
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'owner'");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Owner deleted successfully";
            } else {
                $_SESSION['error_message'] = "Failed to delete owner";
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid owner ID";
}

header("Location: ?page=owners");
exit;
?>