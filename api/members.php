<?php
// api/members.php
// GET ?city=Paris  → returns public member profiles
// POST action=connect  body: { to_user }
// POST action=disconnect body: { to_user }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// POST: connection actions (must be logged in)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/auth_check.php';
    require_login();

    $action  = trim($_POST['action']  ?? '');
    $toUser  = (int)($_POST['to_user'] ?? 0);
    $fromUser = $_SESSION['user_id'];

    if (!$toUser || $toUser === $fromUser) {
        echo json_encode(['success' => false, 'message' => 'Invalid user']);
        exit;
    }

    if ($action === 'connect') {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO connections (from_user, to_user, status)
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$fromUser, $toUser]);
        echo json_encode(['success' => true, 'message' => 'Connection request sent']);
        exit;
    }

    if ($action === 'disconnect') {
        $stmt = $pdo->prepare("
            DELETE FROM connections
            WHERE (from_user = ? AND to_user = ?) OR (from_user = ? AND to_user = ?)
        ");
        $stmt->execute([$fromUser, $toUser, $toUser, $fromUser]);
        echo json_encode(['success' => true, 'message' => 'Disconnected']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// GET: list members
$city = trim($_GET['city'] ?? '');

$where  = [];
$params = [];

// Only show members (not hosts in this view, adjust if needed)
$where[] = "u.role = 'member'";

if ($city) {
    $where[]  = 'u.city = ?';
    $params[] = $city;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.city, u.bio, u.tags, u.avatar, u.status
    FROM users u
    $whereSql
    ORDER BY u.status = 'open' DESC, u.name ASC
");
$stmt->execute($params);
$members = $stmt->fetchAll();

foreach ($members as &$m) {
    $m['tags'] = $m['tags'] ? explode(',', $m['tags']) : [];
}

echo json_encode(['success' => true, 'data' => $members]);
