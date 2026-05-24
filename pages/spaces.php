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

<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="spaces-page">

    <aside class="filters-sidebar">

        <h3>Filters</h3>

        <select id="cityFilter">
            <option value="">All cities</option>
        </select>

        <input type="number" id="maxPrice" placeholder="Max price">

        <button id="applyFilters">Apply Filters</button>

    </aside>

    <section class="spaces-section">
        <div id="spacesGrid" class="spaces-grid"></div>
    </section>

</div>
    <script>
        const CITIES = <?= json_encode($cities) ?>;
    </script>
    <script src="/nomadnest/js/spaces.js"></script>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
