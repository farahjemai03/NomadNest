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
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="dashboard">

    <h1>User Dashboard</h1>

    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total bookings</h3>
            <p id="totalBookings">0</p>
        </div>

        <div class="stat-card">
            <h3>Active subscriptions</h3>
            <p id="activeSubscriptions">0</p>
        </div>

    </div>

    <table>
        <thead>
            <tr>
                <th>Space</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody id="bookingsTable"></tbody>
    </table>

    <script src="/nomadnest/js/dashboard.js"></script>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
