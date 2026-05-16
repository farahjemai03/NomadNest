<?php
// api/spaces.php
// GET — returns filtered list of spaces as JSON
// Query params: city, type, max_price, amenities (comma-separated), page
// Used by Member 2's JS filter on spaces.php

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$city      = trim($_GET['city']      ?? '');
$type      = trim($_GET['type']      ?? '');
$maxPrice  = (float)($_GET['max_price'] ?? 999999);
$amenities = isset($_GET['amenities']) ? array_filter(explode(',', $_GET['amenities'])) : [];
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;
$offset    = ($page - 1) * $perPage;

// Build WHERE clause dynamically
$where  = ['s.price_per_day <= ?'];
$params = [$maxPrice];

if ($city) {
    $where[]  = 's.city = ?';
    $params[] = $city;
}
if ($type) {
    $where[]  = 's.type = ?';
    $params[] = $type;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

// Filter by amenities (space must have ALL requested amenities)
$amenityHaving = '';
if ($amenities) {
    $placeholders  = implode(',', array_fill(0, count($amenities), '?'));
    $amenityJoin   = "JOIN amenities a ON a.space_id = s.id AND a.name IN ($placeholders)";
    $amenityHaving = 'HAVING COUNT(DISTINCT a.name) = ' . count($amenities);
    $params        = array_merge($amenities, $params); // amenity params go first (before WHERE params)
} else {
    $amenityJoin = 'LEFT JOIN amenities a ON a.space_id = s.id';
}

// Count total for pagination
$countSql = "SELECT COUNT(DISTINCT s.id) FROM spaces s $amenityJoin $whereSql $amenityHaving";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Fetch spaces
$sql = "
    SELECT
        s.id, s.name, s.type, s.city, s.address,
        s.price_per_day, s.availability_status, s.image, s.rating,
        GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ',') AS amenities
    FROM spaces s
    $amenityJoin
    $whereSql
    GROUP BY s.id
    $amenityHaving
    ORDER BY s.rating DESC
    LIMIT ? OFFSET ?
";

$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$spaces = $stmt->fetchAll();

// Convert amenities string to array
foreach ($spaces as &$space) {
    $space['amenities']   = $space['amenities'] ? explode(',', $space['amenities']) : [];
    $space['price_per_day'] = (float)$space['price_per_day'];
    $space['rating']        = (float)$space['rating'];
}

echo json_encode([
    'success' => true,
    'data'    => $spaces,
    'meta'    => [
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'pages'    => ceil($total / $perPage),
    ]
]);
