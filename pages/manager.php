<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

require_host();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Host Dashboard — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build KPI cards, revenue chart area, listings grid here -->
    <!-- MEMBER 2: Calls /nomadnest/api/manager.php to load all data -->
    <!--           POSTs to /nomadnest/api/manager.php to approve/reject bookings -->

    <script src="/nomadnest/js/manager.js"></script>
</body>
</html>
