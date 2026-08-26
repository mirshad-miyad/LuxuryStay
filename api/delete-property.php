<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');

header('Content-Type: application/json');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$propertyId = (int) ($_POST['property_id'] ?? 0);

// Verify property exists and belongs to owner
$stmt = $db->prepare("SELECT id, name FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$propertyId, $ownerId]);
$property = $stmt->fetch();

if (!$property) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Property not found']);
    exit;
}

try {
    $db->prepare("UPDATE properties SET is_active = 0, deleted_at = NOW() WHERE id = ? AND owner_id = ?")
        ->execute([$propertyId, $ownerId]);

    echo json_encode([
        'success' => true,
        'message' => 'Property deleted successfully',
        'redirect' => APP_URL . '/owner/properties.php'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting property']);
}
