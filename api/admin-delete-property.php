<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

header('Content-Type: application/json');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$propertyId = (int) ($_POST['property_id'] ?? 0);

// Verify property exists
$stmt = $db->prepare("SELECT id, name FROM properties WHERE id = ?");
$stmt->execute([$propertyId]);
$property = $stmt->fetch();

if (!$property) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Property not found']);
    exit;
}

try {
    // Start transaction
    $db->beginTransaction();

    // Delete bookings associated with rooms of this property
    $db->prepare("DELETE FROM bookings WHERE room_id IN (SELECT id FROM rooms WHERE property_id = ?)")
        ->execute([$propertyId]);

    // Delete room availability records
    $db->prepare("DELETE FROM room_availability WHERE room_id IN (SELECT id FROM rooms WHERE property_id = ?)")
        ->execute([$propertyId]);

    // Delete rooms
    $db->prepare("DELETE FROM rooms WHERE property_id = ?")->execute([$propertyId]);

    // Delete property amenities
    $db->prepare("DELETE FROM property_amenities WHERE property_id = ?")->execute([$propertyId]);

    // Delete property images
    $db->prepare("DELETE FROM property_images WHERE property_id = ?")->execute([$propertyId]);

    // Delete reviews
    $db->prepare("DELETE FROM reviews WHERE property_id = ?")->execute([$propertyId]);

    // Delete the property itself
    $db->prepare("DELETE FROM properties WHERE id = ?")->execute([$propertyId]);

    $db->commit();

    // Clean up uploaded files
    $uploadsPath = ROOT_PATH . '/uploads/properties/' . $propertyId . '/';
    if (is_dir($uploadsPath)) {
        array_map('unlink', glob($uploadsPath . '*'));
        @rmdir($uploadsPath);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Property deleted successfully',
        'redirect' => APP_URL . '/admin/properties.php'
    ]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting property']);
}
