<?php
// api/reviews.php
// POST { space_id, rating, comment } → submit a review
// Only members who have a confirmed booking for that space can review.

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId  = $_SESSION['user_id'];
$spaceId = (int)($_POST['space_id'] ?? 0);
$rating  = (int)($_POST['rating']   ?? 0);
$comment = trim($_POST['comment']   ?? '');

if (!$spaceId || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid space or rating (1–5 required)']);
    exit;
}

// Check user has a confirmed booking for this space
$stmt = $pdo->prepare("
    SELECT id FROM bookings
    WHERE user_id = ? AND space_id = ? AND status = 'confirmed'
    LIMIT 1
");
$stmt->execute([$userId, $spaceId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You can only review spaces you have stayed at']);
    exit;
}

// One review per user per space
$stmt = $pdo->prepare('SELECT id FROM reviews WHERE user_id = ? AND space_id = ?');
$stmt->execute([$userId, $spaceId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this space']);
    exit;
}

// Insert review
$pdo->prepare('INSERT INTO reviews (user_id, space_id, rating, comment) VALUES (?, ?, ?, ?)')
    ->execute([$userId, $spaceId, $rating, $comment]);

// Update the space's cached average rating
$pdo->prepare("
    UPDATE spaces SET rating = (
        SELECT AVG(rating) FROM reviews WHERE space_id = ?
    ) WHERE id = ?
")->execute([$spaceId, $spaceId]);

echo json_encode(['success' => true, 'message' => 'Review submitted, thank you!']);
