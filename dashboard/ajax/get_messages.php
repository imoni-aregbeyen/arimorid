<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode([]));
}

$user1 = (int)$_GET['user1'];
$user2 = (int)$_GET['user2'];
$last_id = (int)$_GET['last'] ?? 0;

$query = "SELECT m.*, u.name as sender_name 
          FROM messages m
          JOIN users u ON m.sender_id = u.id
          WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
          AND m.id > ?
          ORDER BY created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $user1, $user2, $user2, $user1, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);