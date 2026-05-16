<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /nomadnest/pages/spaces.php');
    exit;
}

// Fetch space data server-side for page title and meta
$stmt = $pdo->prepare("SELECT name, city FROM spaces WHERE id = ?");
$stmt->execute([$id]);
$space = $stmt->fetch();

if (!$space) {
    header('Location: /nomadnest/pages/spaces.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($space['name']) ?> — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build gallery, tabs, booking widget UI here -->
    <!-- MEMBER 2: Calls /nomadnest/api/space.php?id=X to load full data -->
    <!--           Calls /nomadnest/api/space.php?id=X&booked=1 for the calendar -->

    <script>
        const SPACE_ID = <?= $id ?>;
    </script>
    <script src="/nomadnest/js/space.js"></script>
</body>
</html>
