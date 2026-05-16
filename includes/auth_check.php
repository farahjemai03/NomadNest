<?php
// includes/auth_check.php
// Include this at the top of any page that requires a logged-in user.
// Usage:
//   require_once __DIR__ . '/../includes/auth_check.php';          // any logged-in user
//   require_once __DIR__ . '/../includes/auth_check.php'; require_host();  // hosts only

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): array|null {
    if (!is_logged_in()) return null;
    return [
        'id'   => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ];
}

function require_login(string $redirect = '/pages/auth.php'): void {
    if (!is_logged_in()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function require_host(string $redirect = '/pages/auth.php'): void {
    require_login($redirect);
    if ($_SESSION['user_role'] !== 'host') {
        header('Location: /index.php');
        exit;
    }
}

function require_member(string $redirect = '/pages/auth.php'): void {
    require_login($redirect);
    if ($_SESSION['user_role'] !== 'member') {
        header('Location: /index.php');
        exit;
    }
}
