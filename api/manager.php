<?php
// api/manager.php
// GET  — host dashboard data (KPIs, revenue chart, pending bookings, reviews, listings)
// POST action=approve|cancel  body: { booking_id }
// POST action=pause|unpause   body: { space_id }
// POST action=delete_listing  body: { space_id }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_host();

$hostId = $_SESSION['user_id'];

// ---- POST actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'approve' || $action === 'cancel') {
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $newStatus = $action === 'approve' ? 'confirmed' : 'cancelled';

        // Verify the booking belongs to one of this host's spaces
        $stmt = $pdo->prepare("
            UPDATE bookings b
            JOIN spaces s ON s.id = b.space_id
            SET b.status = ?
            WHERE b.id = ? AND s.host_id = ?
        ");
        $stmt->execute([$newStatus, $bookingId, $hostId]);

        echo json_encode(['success' => true, 'message' => 'Booking ' . $newStatus]);
        exit;
    }

    if ($action === 'pause' || $action === 'unpause') {
        $spaceId   = (int)($_POST['space_id'] ?? 0);
        $newStatus = $action === 'pause' ? 'full' : 'available';

        $stmt = $pdo->prepare('UPDATE spaces SET availability_status = ? WHERE id = ? AND host_id = ?');
        $stmt->execute([$newStatus, $spaceId, $hostId]);

        echo json_encode(['success' => true, 'message' => 'Listing ' . $action . 'd']);
        exit;
    }

    if ($action === 'delete_listing') {
        $spaceId = (int)($_POST['space_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM spaces WHERE id = ? AND host_id = ?');
        $stmt->execute([$spaceId, $hostId]);
        echo json_encode(['success' => true, 'message' => 'Listing deleted']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// ---- GET: dashboard data ----

// KPI stats for today / all-time
$stmt = $pdo->prepare("
    SELECT
        ROUND(
            SUM(CASE WHEN b.date_start = CURDATE() AND b.status = 'confirmed' THEN 1 ELSE 0 END)
            / NULLIF(SUM(s.id IS NOT NULL), 0) * 100
        , 0) AS occupancy_today,
        SUM(CASE WHEN MONTH(b.date_start) = MONTH(CURDATE()) AND b.status = 'confirmed' THEN b.total_price ELSE 0 END) AS revenue_month,
        COUNT(DISTINCT b.id) AS total_reservations,
        COUNT(DISTINCT b.user_id) AS active_members
    FROM spaces s
    LEFT JOIN bookings b ON b.space_id = s.id
    WHERE s.host_id = ?
");
$stmt->execute([$hostId]);
$kpis = $stmt->fetch();

// Revenue by month (last 8 months) for the chart
$stmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(b.date_start, '%b %Y') AS month_label,
        DATE_FORMAT(b.date_start, '%Y-%m') AS month_key,
        SUM(b.total_price) AS revenue
    FROM bookings b
    JOIN spaces s ON s.id = b.space_id
    WHERE s.host_id = ?
      AND b.status = 'confirmed'
      AND b.date_start >= DATE_SUB(CURDATE(), INTERVAL 8 MONTH)
    GROUP BY month_key, month_label
    ORDER BY month_key ASC
");
$stmt->execute([$hostId]);
$revenueChart = $stmt->fetchAll();

// Pending booking requests
$stmt = $pdo->prepare("
    SELECT b.id, b.date_start, b.date_end, b.seats, b.total_price, b.status,
           s.name AS space_name, u.name AS member_name
    FROM bookings b
    JOIN spaces s ON s.id = b.space_id
    JOIN users  u ON u.id = b.user_id
    WHERE s.host_id = ?
    ORDER BY b.status = 'pending' DESC, b.date_start ASC
    LIMIT 20
");
$stmt->execute([$hostId]);
$bookingRequests = $stmt->fetchAll();

// Recent reviews
$stmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name AS reviewer_name, s.name AS space_name
    FROM reviews r
    JOIN spaces s ON s.id = r.space_id
    JOIN users  u ON u.id = r.user_id
    WHERE s.host_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$hostId]);
$reviews = $stmt->fetchAll();

// Host's listings
$stmt = $pdo->prepare("
    SELECT s.id, s.name, s.city, s.type, s.price_per_day, s.availability_status, s.image, s.rating
    FROM spaces s
    WHERE s.host_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$hostId]);
$listings = $stmt->fetchAll();

echo json_encode([
    'success'         => true,
    'kpis'            => $kpis,
    'revenue_chart'   => $revenueChart,
    'booking_requests'=> $bookingRequests,
    'reviews'         => $reviews,
    'listings'        => $listings,
    'host_name'       => $_SESSION['user_name'],
]);
