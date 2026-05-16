<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$currentUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build conversation sidebar and chat thread UI here -->
    <!-- MEMBER 2: Calls /nomadnest/api/messages.php to load conversations -->
    <!--           Calls /nomadnest/api/messages.php?with=X for a thread -->
    <!--           POSTs to /nomadnest/api/messages.php to send a message -->

    <script>
        const CURRENT_USER_ID = <?= $currentUserId ?>;
    </script>
    <script src="/nomadnest/js/messages.js"></script>
</body>
</html>
