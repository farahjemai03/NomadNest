<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build stat widgets, bookings table, subscription cards here -->
    <!-- MEMBER 2: Calls /nomadnest/api/dashboard.php to load all data -->

    <script src="/nomadnest/js/dashboard.js"></script>
</body>
</html>
