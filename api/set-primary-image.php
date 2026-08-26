<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');

header('Content-Type: application/json');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$propertyId = (int) ($_POST['property_id'] ?? 0);
$imageId = (int) ($_POST['image_id'] ?? 0);

if (!verifyCsrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Verify property belongs to owner
$stmt = $db->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$propertyId, $ownerId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify image exists
$stmt = $db->prepare("SELECT id FROM property_images WHERE id = ? AND property_id = ?");
$stmt->execute([$imageId, $propertyId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Image not found']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Set all images as non-primary
    $db->prepare("UPDATE property_images SET is_primary = 0 WHERE property_id = ?")
        ->execute([$propertyId]);
    
    // Set this image as primary
    $db->prepare("UPDATE property_images SET is_primary = 1 WHERE id = ?")
        ->execute([$imageId]);
    
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Primary image set']);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating primary image']);
}
