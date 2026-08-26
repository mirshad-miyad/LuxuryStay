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

// Get image to delete
$stmt = $db->prepare("SELECT image_path, is_primary FROM property_images WHERE id = ? AND property_id = ?");
$stmt->execute([$imageId, $propertyId]);
$image = $stmt->fetch();

if (!$image) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Image not found']);
    exit;
}

try {
    $wasPrimary = (int) $image['is_primary'] === 1;

    $db->prepare("DELETE FROM property_images WHERE id = ?")
        ->execute([$imageId]);

    if ($wasPrimary) {
        $next = $db->prepare("SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
        $next->execute([$propertyId]);
        $nextImageId = (int) ($next->fetchColumn() ?: 0);
        if ($nextImageId) {
            $db->prepare("UPDATE property_images SET is_primary = 1 WHERE id = ?")->execute([$nextImageId]);
        }
    }

    deleteUploadedFile($image['image_path']);

    echo json_encode(['success' => true, 'message' => 'Image deleted']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting image']);
}
