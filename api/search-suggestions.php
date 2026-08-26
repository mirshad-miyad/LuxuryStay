<?php
/**
 * AJAX search suggestions API
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT DISTINCT name, district FROM properties WHERE status = 'approved' AND (name LIKE ? OR district LIKE ? OR address LIKE ?) LIMIT 8");
$like = "%$q%";
$stmt->execute([$like, $like, $like]);
$results = [];
foreach ($stmt->fetchAll() as $row) {
    $results[] = ['name' => $row['name'], 'district' => $row['district']];
}

// Add district matches
$stmt2 = $db->prepare("SELECT DISTINCT district FROM properties WHERE status = 'approved' AND district LIKE ? LIMIT 5");
$stmt2->execute([$like]);
foreach ($stmt2->fetchAll() as $row) {
    $results[] = ['name' => $row['district'], 'district' => $row['district']];
}

echo json_encode(array_slice(array_unique($results, SORT_REGULAR), 0, 8));
