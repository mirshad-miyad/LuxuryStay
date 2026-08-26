<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$propertyId = (int) ($_GET['property_id'] ?? $_POST['property_id'] ?? 0);
$editRoomId = (int) ($_GET['edit_id'] ?? 0);
$error = '';

$properties = $db->prepare("SELECT id, name FROM properties WHERE owner_id = ? AND deleted_at IS NULL ORDER BY name");
$properties->execute([$ownerId]);
$properties = $properties->fetchAll();

if ($propertyId) {
    $chk = $db->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
    $chk->execute([$propertyId, $ownerId]);
    if (!$chk->fetch()) $propertyId = 0;
}

$amenitiesList = $db->query("SELECT * FROM amenities ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        flash('danger', 'Invalid request.');
        redirect(APP_URL . '/owner/rooms.php?property_id=' . $propertyId);
    }

    $action = $_POST['action'] ?? '';
    $roomId = (int) ($_POST['room_id'] ?? 0);

    if (in_array($action, ['add', 'update'], true)) {
        $targetPropertyId = (int) ($_POST['property_id'] ?? 0);
        $propertyCheck = $db->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
        $propertyCheck->execute([$targetPropertyId, $ownerId]);
        if (!$propertyCheck->fetch()) {
            flash('danger', 'Please select a valid property.');
            redirect(APP_URL . '/owner/rooms.php');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $weekendPrice = trim((string) ($_POST['weekend_price'] ?? ''));
        $weekendPrice = $weekendPrice === '' ? null : (float) $weekendPrice;
        $maxGuests = (int) ($_POST['max_guests'] ?? 0);
        $inventory = (int) ($_POST['inventory'] ?? 0);
        $bedType = trim((string) ($_POST['bed_type'] ?? ''));
        $status = $_POST['status'] ?? 'active';
        $selectedAmenities = array_values(array_unique(array_map('intval', $_POST['room_amenities'] ?? [])));

        $errors = [];
        if ($name === '') $errors[] = 'Room name is required.';
        if ($price <= 0) $errors[] = 'Base room price must be greater than zero.';
        if ($weekendPrice !== null && $weekendPrice <= 0) $errors[] = 'Weekend price must be greater than zero.';
        if ($maxGuests < 1 || $maxGuests > 50) $errors[] = 'Room capacity must be between 1 and 50 guests.';
        if ($inventory < 1 || $inventory > 100) $errors[] = 'Room inventory must be between 1 and 100.';
        if (!in_array($status, ['active', 'inactive'], true)) $errors[] = 'Invalid room status.';

        if ($action === 'update') {
            $roomCheck = $db->prepare("SELECT r.id, r.property_id FROM rooms r JOIN properties p ON r.property_id = p.id WHERE r.id = ? AND p.owner_id = ? AND p.deleted_at IS NULL");
            $roomCheck->execute([$roomId, $ownerId]);
            $room = $roomCheck->fetch();
            if (!$room) $errors[] = 'Room not found.';
            if ($room && (int) $room['property_id'] !== $targetPropertyId) $errors[] = 'Rooms cannot be moved between properties here.';
        }

        if (empty($errors)) {
            try {
                $db->beginTransaction();
                if ($action === 'add') {
                    $db->prepare("INSERT INTO rooms (property_id, name, description, price_per_night, weekend_price, max_guests, inventory, bed_type, status) VALUES (?,?,?,?,?,?,?,?,?)")
                        ->execute([$targetPropertyId, $name, $description, $price, $weekendPrice, $maxGuests, $inventory, $bedType, $status]);
                    $roomId = (int) $db->lastInsertId();
                    $message = 'Room added.';
                } else {
                    $db->prepare("UPDATE rooms SET name = ?, description = ?, price_per_night = ?, weekend_price = ?, max_guests = ?, inventory = ?, bed_type = ?, status = ? WHERE id = ?")
                        ->execute([$name, $description, $price, $weekendPrice, $maxGuests, $inventory, $bedType, $status, $roomId]);
                    $message = 'Room updated.';
                }

                $db->prepare("DELETE FROM room_amenities WHERE room_id = ?")->execute([$roomId]);
                foreach ($selectedAmenities as $amenityId) {
                    $db->prepare("INSERT IGNORE INTO room_amenities (room_id, amenity_id) VALUES (?,?)")
                        ->execute([$roomId, $amenityId]);
                }

                $db->commit();
                flash('success', $message);
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                flash('danger', 'Room could not be saved.');
            }
            redirect(APP_URL . '/owner/rooms.php?property_id=' . $targetPropertyId);
        }

        flash('danger', implode(' ', $errors));
        redirect(APP_URL . '/owner/rooms.php?property_id=' . $targetPropertyId . ($action === 'update' ? '&edit_id=' . $roomId : ''));
    }

    if (in_array($action, ['activate', 'deactivate', 'delete'], true)) {
        $roomCheck = $db->prepare("SELECT r.id, r.property_id FROM rooms r JOIN properties p ON r.property_id = p.id WHERE r.id = ? AND p.owner_id = ? AND p.deleted_at IS NULL");
        $roomCheck->execute([$roomId, $ownerId]);
        $room = $roomCheck->fetch();
        if ($room) {
            if ($action === 'delete') {
                $bookingCount = $db->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ?");
                $bookingCount->execute([$roomId]);
                if ((int) $bookingCount->fetchColumn() > 0) {
                    $db->prepare("UPDATE rooms SET status = 'inactive' WHERE id = ?")->execute([$roomId]);
                    flash('success', 'Room has bookings, so it was deactivated instead of permanently deleted.');
                } else {
                    $roomImages = $db->prepare("SELECT image_path FROM room_images WHERE room_id = ?");
                    $roomImages->execute([$roomId]);
                    foreach ($roomImages->fetchAll() as $image) {
                        deleteUploadedFile($image['image_path']);
                    }
                    $db->prepare("DELETE FROM room_availability WHERE room_id = ?")->execute([$roomId]);
                    $db->prepare("DELETE FROM room_amenities WHERE room_id = ?")->execute([$roomId]);
                    $db->prepare("DELETE FROM room_images WHERE room_id = ?")->execute([$roomId]);
                    $db->prepare("DELETE FROM rooms WHERE id = ?")->execute([$roomId]);
                    flash('success', 'Room deleted.');
                }
            } else {
                $status = $action === 'activate' ? 'active' : 'inactive';
                $db->prepare("UPDATE rooms SET status = ? WHERE id = ?")->execute([$status, $roomId]);
                flash('success', 'Room ' . $status . '.');
            }
            redirect(APP_URL . '/owner/rooms.php?property_id=' . (int) $room['property_id']);
        }
    }

    flash('danger', 'Room action could not be completed.');
    redirect(APP_URL . '/owner/rooms.php?property_id=' . $propertyId);
}

$rooms = [];
$editRoom = null;
$selectedRoomAmenityIds = [];
if ($propertyId) {
    $stmt = $db->prepare("SELECT r.*,
        GROUP_CONCAT(a.name ORDER BY a.name SEPARATOR ', ') AS amenities
        FROM rooms r
        LEFT JOIN room_amenities ra ON ra.room_id = r.id
        LEFT JOIN amenities a ON a.id = ra.amenity_id
        WHERE r.property_id = ?
        GROUP BY r.id
        ORDER BY r.status ASC, r.name ASC");
    $stmt->execute([$propertyId]);
    $rooms = $stmt->fetchAll();

    if ($editRoomId) {
        $editStmt = $db->prepare("SELECT r.* FROM rooms r JOIN properties p ON r.property_id = p.id WHERE r.id = ? AND r.property_id = ? AND p.owner_id = ? AND p.deleted_at IS NULL");
        $editStmt->execute([$editRoomId, $propertyId, $ownerId]);
        $editRoom = $editStmt->fetch();
        if ($editRoom) {
            $amenityStmt = $db->prepare("SELECT amenity_id FROM room_amenities WHERE room_id = ?");
            $amenityStmt->execute([$editRoomId]);
            $selectedRoomAmenityIds = array_map('intval', array_column($amenityStmt->fetchAll(), 'amenity_id'));
        }
    }
}

$pageTitle = 'Manage Rooms';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
                <h1 class="mb-0">Manage <span class="text-gold">Rooms</span></h1>
                <?php if ($propertyId): ?><a href="<?= APP_URL ?>/owner/availability.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar3 me-1"></i>Availability</a><?php endif; ?>
            </div>

            <form method="GET" class="mb-4">
                <select name="property_id" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
                    <option value="">Select Property</option>
                    <?php foreach ($properties as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $propertyId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($propertyId): ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="form-dark">
                        <h5><?= $editRoom ? 'Edit Room' : 'Add Room' ?></h5>
                        <form method="POST" data-loading>
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="property_id" value="<?= $propertyId ?>">
                            <input type="hidden" name="action" value="<?= $editRoom ? 'update' : 'add' ?>">
                            <?php if ($editRoom): ?><input type="hidden" name="room_id" value="<?= $editRoom['id'] ?>"><?php endif; ?>
                            <div class="row g-2">
                                <div class="col-12"><label class="form-label">Room Name *</label><input type="text" name="name" class="form-control" value="<?= e($editRoom['name'] ?? '') ?>" required maxlength="150"></div>
                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($editRoom['description'] ?? '') ?></textarea></div>
                                <div class="col-md-6"><label class="form-label">Base Price *</label><input type="number" name="price" class="form-control" value="<?= e((string) ($editRoom['price_per_night'] ?? '')) ?>" required min="1" step="0.01"></div>
                                <div class="col-md-6"><label class="form-label">Weekend Price</label><input type="number" name="weekend_price" class="form-control" value="<?= e((string) ($editRoom['weekend_price'] ?? '')) ?>" min="1" step="0.01"></div>
                                <div class="col-md-4"><label class="form-label">Guests *</label><input type="number" name="max_guests" class="form-control" value="<?= e((string) ($editRoom['max_guests'] ?? 2)) ?>" min="1" max="50" required></div>
                                <div class="col-md-4"><label class="form-label">Inventory *</label><input type="number" name="inventory" class="form-control" value="<?= e((string) ($editRoom['inventory'] ?? 1)) ?>" min="1" max="100" required></div>
                                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($editRoom['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($editRoom['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                                <div class="col-12"><label class="form-label">Bed Information</label><input type="text" name="bed_type" class="form-control" value="<?= e($editRoom['bed_type'] ?? '') ?>" placeholder="King, Queen, 2 Singles" maxlength="50"></div>
                                <div class="col-12">
                                    <label class="form-label">Room Amenities</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($amenitiesList as $a): ?>
                                        <label class="amenity-badge"><input type="checkbox" name="room_amenities[]" value="<?= $a['id'] ?>" <?= in_array((int) $a['id'], $selectedRoomAmenityIds, true) ? 'checked' : '' ?>> <?= e($a['name']) ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-gold btn-sm"><?= $editRoom ? 'Save Room' : 'Add Room' ?></button>
                                <?php if ($editRoom): ?><a href="<?= APP_URL ?>/owner/rooms.php?property_id=<?= $propertyId ?>" class="btn btn-outline-secondary btn-sm">Cancel</a><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="luxury-card p-4 table-responsive">
                        <table class="table table-dark table-sm align-middle">
                            <thead><tr><th>Name</th><th>Pricing</th><th>Capacity</th><th>Amenities</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($rooms as $r): ?>
                                <tr>
                                    <td><strong><?= e($r['name']) ?></strong><br><small class="text-muted"><?= e($r['bed_type']) ?></small></td>
                                    <td><?= formatPrice((float) $r['price_per_night']) ?><br><small class="text-muted"><?= $r['weekend_price'] ? 'Weekend ' . formatPrice((float) $r['weekend_price']) : 'No weekend override' ?></small></td>
                                    <td><?= (int) $r['max_guests'] ?> guests<br><small class="text-muted"><?= (int) $r['inventory'] ?> room(s)</small></td>
                                    <td><small><?= e($r['amenities'] ?: 'None') ?></small></td>
                                    <td><span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($r['status']) ?></span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a href="<?= APP_URL ?>/owner/rooms.php?property_id=<?= $propertyId ?>&edit_id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-gold">Edit</a>
                                            <a href="<?= APP_URL ?>/owner/availability.php?room_id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Dates</a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="property_id" value="<?= $propertyId ?>">
                                                <button name="action" value="<?= $r['status'] === 'active' ? 'deactivate' : 'activate' ?>" class="btn btn-sm btn-outline-secondary"><?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                                                <button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this room type? Rooms with bookings will be deactivated instead.');">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rooms)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No rooms for this property yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
