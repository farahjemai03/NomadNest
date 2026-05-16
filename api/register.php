<?php
// api/register.php
// POST { name, email, password, role }
// Returns JSON { success, message, user? }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';
$role     = trim($_POST['role']     ?? 'member');

// Basic validation
if (!$name || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}
if (!in_array($role, ['member', 'host'])) {
    $role = 'member';
}

// Check if email already exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Insert user
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $hash, $role]);
$userId = $pdo->lastInsertId();

// Create free subscription automatically
$pdo->prepare('INSERT INTO subscriptions (user_id, plan, price) VALUES (?, "free", 0.00)')
    ->execute([$userId]);

// Log the user in immediately
$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = $role;

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully',
    'user'    => ['id' => $userId, 'name' => $name, 'role' => $role]
]);
