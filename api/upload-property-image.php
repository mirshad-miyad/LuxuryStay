<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');

header('Content-Type: application/json');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$propertyId = (int) ($_POST['property_id'] ?? 0);

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

$uploadedCount = 0;
$errors = [];

if (!empty($_FILES['images']['name'][0])) {
    $primaryStmt = $db->prepare("SELECT COUNT(*) FROM property_images WHERE property_id = ? AND is_primary = 1");
    $primaryStmt->execute([$propertyId]);
    $hasPrimary = (int) $primaryStmt->fetchColumn() > 0;

    $sortStmt = $db->prepare("SELECT COALESCE(MAX(sort_order) + 1, 0) FROM property_images WHERE property_id = ?");
    $sortStmt->execute([$propertyId]);
    $nextSort = (int) $sortStmt->fetchColumn();

    foreach ($_FILES['images']['name'] as $i => $fname) {
        $file = [
            'name' => $fname,
            'type' => $_FILES['images']['type'][$i] ?? '',
            'tmp_name' => $_FILES['images']['tmp_name'][$i] ?? '',
            'error' => $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $_FILES['images']['size'][$i] ?? 0
        ];
        if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) continue;

        $validationError = validateUploadedImage($file);
        if ($validationError) {
            $errors[] = ($fname ?: 'Image') . ' (' . $validationError . ')';
            continue;
        }

        $path = uploadImage($file, PROPERTY_UPLOAD . $propertyId . DIRECTORY_SEPARATOR);
        if ($path) {
            $isPrimary = !$hasPrimary && $uploadedCount === 0 ? 1 : 0;
            $db->prepare("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?,?,?,?)")
                ->execute([$propertyId, $path, $isPrimary, $nextSort]);
            $hasPrimary = $hasPrimary || (bool) $isPrimary;
            $nextSort++;
            $uploadedCount++;
        } else {
            $errors[] = ($fname ?: 'Image') . ' (could not be saved)';
        }
    }
}

if ($uploadedCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => $uploadedCount . ' image(s) uploaded successfully'
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No images uploaded. ' . implode(', ', $errors)
    ]);
}
