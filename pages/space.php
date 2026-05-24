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

    <div class="space-details">

    <div id="spaceGallery" class="gallery"></div>

    <div class="space-info">
        <h1 id="spaceName"></h1>
        <p id="spaceDescription"></p>
        <p id="spacePrice"></p>
    </div>

    <div class="booking-widget">

        <h3>Book this space</h3>

        <input type="date" id="checkIn">
        <input type="date" id="checkOut">

        <button id="bookBtn">Book Now</button>

    </div>

    <div id="reviewsContainer"></div>

</div>
    <script>
        const SPACE_ID = <?= $id ?>;
    </script>
    <script src="/nomadnest/js/space.js"></script>
</body>
</html>
