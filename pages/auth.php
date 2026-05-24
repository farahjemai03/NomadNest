<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// If already logged in, redirect to the right dashboard
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
<body>

   <div class="auth-container">

    <h1>NomadNest</h1>

    <div class="auth-tabs">
        <button id="loginTab">Login</button>
        <button id="registerTab">Register</button>
    </div>

    <form id="loginForm">
        <input type="email" id="loginEmail" placeholder="Email">
        <input type="password" id="loginPassword" placeholder="Password">
        <button type="submit">Login</button>
    </form>

    <form id="registerForm" class="hidden">
        <input type="text" id="registerName" placeholder="Full name">
        <input type="email" id="registerEmail" placeholder="Email">
        <input type="password" id="registerPassword" placeholder="Password">

        <select id="registerRole">
            <option value="member">Member</option>
            <option value="host">Host</option>
        </select>

        <button type="submit">Create account</button>
    </form>

    <p id="authMessage"></p>

</div>

    <script src="/nomadnest/js/auth.js"></script>
</body>
</html>
