<?php
// pg/chat.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

$current_user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Get conversation list
function getConversations($conn, $user_id) {
    $query = "SELECT u.id, u.name, u.role, 
              (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
              (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = u.id AND is_read = 0) as unread_count
              FROM users u
              WHERE u.id != ?
              ORDER BY (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) 
              OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get messages between two users
function getMessages($conn, $user1, $user2) {
    $query = "SELECT m.*, u.name as sender_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
              ORDER BY created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
    $stmt->execute();
    return $stmt->get_result();
}

// Mark messages as read
function markAsRead($conn, $sender_id, $receiver_id) {
    $query = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    $stmt->execute();
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = (int)$_POST['receiver_id'];
    
    if (!empty($message)) {
        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $current_user_id, $receiver_id, $message);
        $stmt->execute();
        
        // Redirect to prevent form resubmission
        header("Location: ?page=chat&with=".$receiver_id);
        exit;
    }
}

// Get current conversation partner
$with_user = isset($_GET['with']) ? (int)$_GET['with'] : 0;
if ($with_user > 0) {
    markAsRead($conn, $with_user, $current_user_id);
    $messages = getMessages($conn, $current_user_id, $with_user);
}

$conversations = getConversations($conn, $current_user_id);
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Conversations</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php while ($conv = $conversations->fetch_assoc()): ?>
                        <a href="?page=chat&with=<?= $conv['id'] ?>" 
                           class="list-group-item list-group-item-action <?= ($with_user == $conv['id']) ? 'active' : '' ?>">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avtar avtar-s rounded-circle bg-light-primary">
                                        <i class="ti ti-user f-18"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?= htmlspecialchars($conv['name']) ?></h6>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($conv['last_message'] ?? 'No messages yet') ?></p>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $conv['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <?php if ($with_user > 0): 
                    $user_info = $conn->query("SELECT name, role FROM users WHERE id = $with_user")->fetch_assoc();
                ?>
                    <h5>Chat with <?= htmlspecialchars($user_info['name']) ?> (<?= ucfirst($user_info['role']) ?>)</h5>
                <?php else: ?>
                    <h5>Select a conversation</h5>
                <?php endif; ?>
            </div>
            
            <div class="card-body chat-body" style="height: 400px; overflow-y: auto;">
                <?php if ($with_user > 0): ?>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <div class="d-flex mb-3 <?= ($msg['sender_id'] == $current_user_id) ? 'justify-content-end' : '' ?>">
                            <div class="message <?= ($msg['sender_id'] == $current_user_id) ? 'bg-primary text-white' : 'bg-light' ?> 
                                p-3 rounded" style="max-width: 70%;">
                                <div class="message-text"><?= htmlspecialchars($msg['message']) ?></div>
                                <div class="text-end small text-muted mt-1">
                                    <?= date('h:i A', strtotime($msg['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ti ti-message-circle fs-1 text-muted"></i>
                        <p class="mt-3">Select a conversation to start chatting</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($with_user > 0): ?>
                <div class="card-footer">
                    <form method="POST" action="?page=chat&with=<?= $with_user ?>">
                        <div class="input-group">
                            <input type="hidden" name="receiver_id" value="<?= $with_user ?>">
                            <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
                            <button class="btn btn-primary" type="submit">Send</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .message {
        word-wrap: break-word;
    }
</style>

<script>
// Auto-scroll to bottom of chat
document.addEventListener('DOMContentLoaded', function() {
    const chatBody = document.querySelector('.chat-body');
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
    
    // Optional: Add real-time updates with AJAX
    setInterval(function() {
        if (<?= $with_user ?>) {
            fetch(`ajax/get_messages.php?user1=<?= $current_user_id ?>&user2=<?= $with_user ?>&last=${$('.message').last().data('id') || 0}`)
                .then(response => response.json())
                .then(messages => {
                    if (messages.length > 0) {
                        messages.forEach(msg => {
                            const isMe = msg.sender_id == <?= $current_user_id ?>;
                            const html = `
                                <div class="d-flex mb-3 ${isMe ? 'justify-content-end' : ''}">
                                    <div class="message ${isMe ? 'bg-primary text-white' : 'bg-light'} p-3 rounded" style="max-width: 70%;">
                                        <div class="message-text">${msg.message}</div>
                                        <div class="text-end small text-muted mt-1">
                                            ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        </div>
                                    </div>
                                </div>`;
                            chatBody.insertAdjacentHTML('beforeend', html);
                        });
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }
                });
        }
    }, 5000); // Poll every 5 seconds
});
</script>