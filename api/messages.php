<?php
// api/messages.php
// GET              → list of conversations for logged-in user
// GET ?with=userId → full thread with that user + marks as read
// POST { to, body }  → send a message

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$userId = $_SESSION['user_id'];

// ---- SEND MESSAGE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toId = (int)($_POST['to']   ?? 0);
    $body = trim($_POST['body']  ?? '');

    if (!$toId || !$body) {
        echo json_encode(['success' => false, 'message' => 'Recipient and message body required']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $toId, $body]);

    echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
    exit;
}

// ---- GET THREAD ----
if (isset($_GET['with'])) {
    $withId = (int)$_GET['with'];

    // Mark messages from the other person as read
    $pdo->prepare("
        UPDATE messages SET is_read = 1
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ")->execute([$withId, $userId]);

    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.body, m.created_at, m.is_read,
               u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$userId, $withId, $withId, $userId]);
    $messages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $messages]);
    exit;
}

// ---- GET CONVERSATION LIST ----
// Latest message per conversation partner
$stmt = $pdo->prepare("
    SELECT
        other.id   AS partner_id,
        other.name AS partner_name,
        other.avatar AS partner_avatar,
        latest.body AS last_message,
        latest.created_at,
        SUM(CASE WHEN m2.is_read = 0 AND m2.receiver_id = ? THEN 1 ELSE 0 END) AS unread_count
    FROM messages m
    JOIN users other ON other.id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
    JOIN messages latest ON latest.id = (
        SELECT id FROM messages
        WHERE (sender_id = ? AND receiver_id = other.id)
           OR (sender_id = other.id AND receiver_id = ?)
        ORDER BY created_at DESC LIMIT 1
    )
    LEFT JOIN messages m2 ON m2.sender_id = other.id AND m2.receiver_id = ?
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other.id, other.name, other.avatar, latest.body, latest.created_at
    ORDER BY latest.created_at DESC
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $conversations]);
