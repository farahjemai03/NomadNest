<?php
// api/dashboard.php
// GET — returns logged-in member's dashboard data
// { bookings[], stats{}, subscription{} }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$userId = $_SESSION['user_id'];

// Bookings list
$stmt = $pdo->prepare("
    SELECT b.id, b.date_start, b.date_end, b.seats, b.total_price, b.status,
           s.name AS space_name, s.city
    FROM bookings b
    JOIN spaces s ON s.id = b.space_id
    WHERE b.user_id = ?
    ORDER BY b.date_start DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Stats
$stmt = $pdo->prepare("
    SELECT
        SUM(CASE WHEN status IN ('confirmed','pending') AND date_end >= CURDATE() THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN MONTH(date_start) = MONTH(CURDATE()) AND YEAR(date_start) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_month,
        SUM(CASE WHEN status = 'confirmed' THEN DATEDIFF(date_end, date_start) + 1 ELSE 0 END) * 8 AS hours_logged
    FROM bookings
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

// Subscription
$stmt = $pdo->prepare('SELECT plan, price, active FROM subscriptions WHERE user_id = ?');
$stmt->execute([$userId]);
$subscription = $stmt->fetch() ?? ['plan' => 'free', 'price' => 0, 'active' => 1];

echo json_encode([
    'success'      => true,
    'bookings'     => $bookings,
    'stats'        => [
        'active'      => (int)$stats['active'],
        'this_month'  => (int)$stats['this_month'],
        'hours_logged'=> (int)$stats['hours_logged'],
    ],
    'subscription' => $subscription,
    'user_name'    => $_SESSION['user_name'],
]);
