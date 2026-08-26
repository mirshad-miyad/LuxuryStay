<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');

header('Content-Type: application/json');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$propertyId = (int) ($_POST['property_id'] ?? 0);
$order = $_POST['order'] ?? [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$stmt = $db->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$propertyId, $ownerId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!is_array($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image order']);
    exit;
}

$imageIds = array_values(array_unique(array_filter(array_map('intval', $order))));
if (empty($imageIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No images selected']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($imageIds), '?'));
$verify = $db->prepare("SELECT id FROM property_images WHERE property_id = ? AND id IN ($placeholders)");
$verify->execute(array_merge([$propertyId], $imageIds));
$validIds = array_map('intval', array_column($verify->fetchAll(), 'id'));
sort($validIds);
$expected = $imageIds;
sort($expected);

if ($validIds !== $expected) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Image order contains invalid images']);
    exit;
}

try {
    $db->beginTransaction();
    $update = $db->prepare("UPDATE property_images SET sort_order = ? WHERE id = ? AND property_id = ?");
    foreach ($imageIds as $sort => $imageId) {
        $update->execute([$sort, $imageId, $propertyId]);
    }
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Image order saved']);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Image order could not be saved']);
}
