<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Fetch all unique cities for the filter dropdown
$cities = $pdo->query("SELECT DISTINCT city FROM spaces ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spaces — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <!-- MEMBER 1: Build the filter sidebar and space card grid here -->
    <!-- MEMBER 2: Calls /nomadnest/api/spaces.php with filters and renders cards -->

    <!-- Pass cities to JS -->
    <script>
        const CITIES = <?= json_encode($cities) ?>;
    </script>
    <script src="/nomadnest/js/spaces.js"></script>
</body>
</html>
