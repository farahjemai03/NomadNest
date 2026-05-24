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

    <div class="members-page">

    <h1>Members</h1>

    <div class="members-filters">
        <select id="memberCityFilter">
            <option value="">All cities</option>
        </select>
    </div>

    <div id="membersGrid"></div>

</div>

    <script>
        const CITIES = <?= json_encode($cities) ?>;
        const CURRENT_USER_ID = <?= json_encode($currentUserId) ?>;
    </script>
    <script src="/nomadnest/js/members.js"></script>
</body>
</html>
