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

    <!-- MEMBER 1: Build the sign in / register form UI here -->
    <!-- MEMBER 2: Add the form toggle JS and validation here -->

    <script src="/nomadnest/js/auth.js"></script>
</body>
</html>
