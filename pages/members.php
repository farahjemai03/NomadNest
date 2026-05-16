<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$cities = $pdo->query("SELECT DISTINCT city FROM users WHERE role = 'member' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);

$currentUserId = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build member cards and city filter tabs here -->
    <!-- MEMBER 2: Calls /nomadnest/api/members.php?city=X to load members -->

    <script>
        const CITIES = <?= json_encode($cities) ?>;
        const CURRENT_USER_ID = <?= json_encode($currentUserId) ?>;
    </script>
    <script src="/nomadnest/js/members.js"></script>
</body>
</html>
