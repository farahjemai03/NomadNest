<?php
// api/book.php
// POST { space_id, date_start, date_end, seats }
// Creates a booking, returns JSON { success, message, booking? }

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$spaceId   = (int)($_POST['space_id']   ?? 0);
$dateStart = trim($_POST['date_start']  ?? '');
$dateEnd   = trim($_POST['date_end']    ?? '');
$seats     = max(1, (int)($_POST['seats'] ?? 1));
$userId    = $_SESSION['user_id'];

// Validate inputs
if (!$spaceId || !$dateStart || !$dateEnd) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$start = DateTime::createFromFormat('Y-m-d', $dateStart);
$end   = DateTime::createFromFormat('Y-m-d', $dateEnd);

if (!$start || !$end || $start > $end || $start < new DateTime('today')) {
    echo json_encode(['success' => false, 'message' => 'Invalid dates']);
    exit;
}

// Fetch space to get price and check it exists
$stmt = $pdo->prepare('SELECT id, price_per_day, availability_status FROM spaces WHERE id = ?');
$stmt->execute([$spaceId]);
$space = $stmt->fetch();

if (!$space) {
    echo json_encode(['success' => false, 'message' => 'Space not found']);
    exit;
}
if ($space['availability_status'] === 'full') {
    echo json_encode(['success' => false, 'message' => 'This space is fully booked']);
    exit;
}

// Conflict check — no overlapping confirmed/pending bookings
$stmt = $pdo->prepare("
    SELECT id FROM bookings
    WHERE space_id = ?
      AND status IN ('confirmed', 'pending')
      AND date_start <= ?
      AND date_end   >= ?
");
$stmt->execute([$spaceId, $dateEnd, $dateStart]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Those dates are already booked']);
    exit;
}

// Calculate price
$days        = $start->diff($end)->days + 1;
$basePrice   = $days * $seats * $space['price_per_day'];
$serviceFee  = round($basePrice * 0.09, 2); // 9% service fee
$totalPrice  = $basePrice + $serviceFee;

// Insert booking
$stmt = $pdo->prepare("
    INSERT INTO bookings (user_id, space_id, date_start, date_end, seats, total_price, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([$userId, $spaceId, $dateStart, $dateEnd, $seats, $totalPrice]);
$bookingId = $pdo->lastInsertId();

echo json_encode([
    'success' => true,
    'message' => 'Booking request submitted! Awaiting confirmation.',
    'booking' => [
        'id'          => $bookingId,
        'date_start'  => $dateStart,
        'date_end'    => $dateEnd,
        'seats'       => $seats,
        'days'        => $days,
        'base_price'  => $basePrice,
        'service_fee' => $serviceFee,
        'total_price' => $totalPrice,
        'status'      => 'pending',
    ]
]);
