<?php
/* api/members.php
   Handles GET (list/filter members) and POST (send connection request)
*/
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// ── POST: send connection request ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Connecte-toi avant']);
        exit;
    }

    $fromUser = (int) $_SESSION['user_id'];
    $toUser   = (int) ($_POST['to_user'] ?? 0);

    if (!$toUser || $toUser === $fromUser) {
        echo json_encode(['success' => false, 'message' => 'Requête invalide']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO connections (from_user, to_user, status)
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$fromUser, $toUser]);

        if ($stmt->rowCount()) {
            echo json_encode(['success' => true, 'message' => 'Invitation envoyée !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invitation déjà envoyée']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
    }
    exit;
}

// ── GET: fetch members ────────────────────────────────────────
$city = trim($_GET['city'] ?? '');

try {
    if ($city !== '') {
        $stmt = $pdo->prepare("
            SELECT id, name, city, bio, tags, status, avatar
            FROM users
            WHERE role = 'member' AND city = ?
            ORDER BY name
        ");
        $stmt->execute([$city]);
    } else {
        $stmt = $pdo->query("
            SELECT id, name, city, bio, tags, status, avatar
            FROM users
            WHERE role = 'member'
            ORDER BY name
        ");
    }

    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Split tags string into array for JS
    foreach ($members as &$m) {
        $m['tags'] = $m['tags'] ? array_map('trim', explode(',', $m['tags'])) : [];
    }
    unset($m);

    echo json_encode(['success' => true, 'data' => $members]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur', 'data' => []]);
}