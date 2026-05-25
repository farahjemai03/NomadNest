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
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    <div class="manager-dashboard">

    <h1>Manager Dashboard</h1>

    <div class="kpi-grid">

        <div class="kpi-card">
            <h3>Total Revenue</h3>
            <p id="totalRevenue"></p>
        </div>

        <div class="kpi-card">
            <h3>Total Bookings</h3>
            <p id="managerBookings"></p>
        </div>

    </div>

    <h2>Your Listings</h2>
    <div id="listingsGrid"></div>

    <h2>Pending Bookings</h2>
    <div id="pendingBookings"></div>

</div>

    <script src="/nomadnest/js/manager.js"></script>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
