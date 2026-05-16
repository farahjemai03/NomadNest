<?php
// api/space.php
// GET ?id=1            → full space detail (info + amenities + reviews + host)
// GET ?id=1&booked=1   → just the booked dates for the calendar (Member 2 uses this)

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Space ID required']);
    exit;
}

// --- Booked dates only (for the calendar widget) ---
if (isset($_GET['booked'])) {
    $stmt = $pdo->prepare("
        SELECT date_start, date_end
        FROM bookings
        WHERE space_id = ? AND status IN ('confirmed', 'pending')
          AND date_end >= CURDATE()
    ");
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll();

    // Expand ranges into individual date strings so JS can highlight them easily
    $dates = [];
    foreach ($rows as $row) {
        $start = new DateTime($row['date_start']);
        $end   = new DateTime($row['date_end']);
        $end->modify('+1 day'); // DatePeriod end is exclusive
        $interval = new DateInterval('P1D');
        foreach (new DatePeriod($start, $interval, $end) as $date) {
            $dates[] = $date->format('Y-m-d');
        }
    }
    echo json_encode(['success' => true, 'booked_dates' => array_unique($dates)]);
    exit;
}

// --- Full space detail ---
$stmt = $pdo->prepare("
    SELECT s.*, u.name AS host_name, u.avatar AS host_avatar
    FROM spaces s
    JOIN users u ON u.id = s.host_id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$space = $stmt->fetch();

if (!$space) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Space not found']);
    exit;
}

// Amenities
$stmt = $pdo->prepare('SELECT name FROM amenities WHERE space_id = ? ORDER BY name');
$stmt->execute([$id]);
$space['amenities'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Reviews (latest 10)
$stmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name AS reviewer_name, u.avatar AS reviewer_avatar
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.space_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$id]);
$space['reviews'] = $stmt->fetchAll();

// Average rating and count
$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE space_id = ?');
$stmt->execute([$id]);
$ratingData = $stmt->fetch();
$space['avg_rating']    = round((float)$ratingData['avg_rating'], 1);
$space['review_count']  = (int)$ratingData['total'];
$space['price_per_day'] = (float)$space['price_per_day'];

echo json_encode(['success' => true, 'data' => $space]);
