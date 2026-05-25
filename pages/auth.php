<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'host') {
        header('Location: /nomadnest/pages/manager.php');
    } else {
        header('Location: /nomadnest/pages/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign in — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">

        <div class="auth-logo">Nomad<span>Nest</span></div>

        <div class="auth-tabs">
            <button id="loginTab" class="active">Login</button>
            <button id="registerTab">Register</button>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label for="loginEmail">Email</label>
                <input type="email" id="loginEmail" placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" id="loginPassword" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Login</button>
        </form>

        <form id="registerForm" class="hidden">
            <div class="form-group">
                <label for="registerName">Full name</label>
                <input type="text" id="registerName" placeholder="Jane Smith">
            </div>
            <div class="form-group">
                <label for="registerEmail">Email</label>
                <input type="email" id="registerEmail" placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="registerPassword">Password</label>
                <input type="password" id="registerPassword" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="registerRole">I am a</label>
                <select id="registerRole">
                    <option value="member">Member</option>
                    <option value="host">Host</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Create account</button>
        </form>

        <p id="authMessage"></p>

    </div>

    <script src="/nomadnest/js/auth.js"></script>
</body>
</html>