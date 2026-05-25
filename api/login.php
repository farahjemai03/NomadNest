<?php
// api/login.php
// POST { email, password }
// Returns JSON { success, message, user? }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password']);
    exit;
}

// Regenerate session ID to prevent fixation attacks
session_regenerate_id(true);

$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

// Redirect destination based on role
$redirect = $user['role'] === 'host' ? '/nomadnest/pages/manager.php' : '/nomadnest/pages/dashboard.php';

echo json_encode([
    'success'  => true,
    'message'  => 'Welcome back, ' . $user['name'] . '!',
    'redirect' => $redirect,
    'user'     => ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']]
]);
