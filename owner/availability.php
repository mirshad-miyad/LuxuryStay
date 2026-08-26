<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];

$roomsStmt = $db->prepare("SELECT r.id, r.name, r.inventory, r.price_per_night, r.weekend_price, p.name as property_name
    FROM rooms r
    JOIN properties p ON r.property_id = p.id
    WHERE p.owner_id = ? AND p.deleted_at IS NULL
    ORDER BY p.name, r.name");
$roomsStmt->execute([$ownerId]);
$rooms = $roomsStmt->fetchAll();
$roomId = (int) ($_GET['room_id'] ?? $_POST['room_id'] ?? ($rooms[0]['id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $availabilityId = (int) ($_POST['availability_id'] ?? 0);
        $check = $db->prepare("SELECT ra.id, ra.room_id FROM room_availability ra JOIN rooms r ON ra.room_id = r.id JOIN properties p ON r.property_id = p.id WHERE ra.id = ? AND p.owner_id = ? AND p.deleted_at IS NULL");
        $check->execute([$availabilityId, $ownerId]);
        $entry = $check->fetch();
        if ($entry) {
            $db->prepare("DELETE FROM room_availability WHERE id = ?")->execute([$availabilityId]);
            flash('success', 'Availability entry removed.');
            redirect(APP_URL . '/owner/availability.php?room_id=' . (int) $entry['room_id']);
        }
        flash('danger', 'Availability entry not found.');
        redirect(APP_URL . '/owner/availability.php?room_id=' . $roomId);
    }

    $roomId = (int) ($_POST['room_id'] ?? 0);
    $date = trim((string) ($_POST['date'] ?? ''));
    $available = (int) ($_POST['is_available'] ?? 1);
    $customPriceRaw = trim((string) ($_POST['custom_price'] ?? ''));
    $customPrice = $customPriceRaw === '' ? null : (float) $customPriceRaw;

    $errors = [];
    $chk = $db->prepare("SELECT r.id FROM rooms r JOIN properties p ON r.property_id = p.id WHERE r.id = ? AND p.owner_id = ? AND p.deleted_at IS NULL");
    $chk->execute([$roomId, $ownerId]);
    if (!$chk->fetch()) $errors[] = 'Please select a valid room.';

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        $errors[] = 'Please select a valid date.';
    } elseif ($date < date('Y-m-d')) {
        $errors[] = 'Availability dates cannot be in the past.';
    }

    if (!in_array($available, [0, 1], true)) $errors[] = 'Invalid availability status.';
    if ($customPrice !== null && $customPrice <= 0) $errors[] = 'Custom price must be greater than zero.';
    if ($available === 0) $customPrice = null;

    if (empty($errors)) {
        $db->prepare("INSERT INTO room_availability (room_id, date, is_available, custom_price)
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE is_available = VALUES(is_available), custom_price = VALUES(custom_price)")
            ->execute([$roomId, $date, $available, $customPrice]);
        flash('success', 'Availability updated.');
    } else {
        flash('danger', implode(' ', $errors));
    }
    redirect(APP_URL . '/owner/availability.php?room_id=' . $roomId);
}

$selectedRoom = null;
foreach ($rooms as $room) {
    if ((int) $room['id'] === $roomId) {
        $selectedRoom = $room;
        break;
    }
}

$entries = [];
if ($selectedRoom) {
    $entriesStmt = $db->prepare("SELECT * FROM room_availability WHERE room_id = ? AND date >= CURDATE() ORDER BY date ASC");
    $entriesStmt->execute([$roomId]);
    $entries = $entriesStmt->fetchAll();
}

$pageTitle = 'Availability';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Availability <span class="text-gold">Calendar</span></h1>
            <?php if (empty($rooms)): ?>
                <div class="alert alert-info">Add a room before managing availability.</div>
            <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="form-dark">
                        <form method="GET" class="mb-3">
                            <label class="form-label">Room</label>
                            <select name="room_id" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $roomId == $r['id'] ? 'selected' : '' ?>><?= e($r['property_name']) ?> - <?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <?php if ($selectedRoom): ?>
                        <div class="alert alert-light border small">
                            Base <?= formatPrice((float) $selectedRoom['price_per_night']) ?>
                            <?= $selectedRoom['weekend_price'] ? ' | Weekend ' . formatPrice((float) $selectedRoom['weekend_price']) : '' ?>
                            | Inventory <?= (int) $selectedRoom['inventory'] ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" data-loading>
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="room_id" value="<?= $roomId ?>">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Availability</label>
                                <select name="is_available" id="availabilitySelect" class="form-select">
                                    <option value="1">Available</option>
                                    <option value="0">Blocked</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Custom Price</label>
                                <input type="number" name="custom_price" id="customPriceInput" class="form-control" min="1" step="0.01" placeholder="Leave blank to use normal pricing">
                                <small class="text-muted">Custom prices are ignored when the date is blocked.</small>
                            </div>
                            <button type="submit" class="btn btn-gold">Save Date</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="luxury-card p-4 table-responsive">
                        <h5 class="text-gold mb-3">Upcoming Date Overrides</h5>
                        <table class="table table-dark table-sm align-middle">
                            <thead><tr><th>Date</th><th>Status</th><th>Custom Price</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td><?= e($entry['date']) ?></td>
                                    <td><span class="badge bg-<?= (int) $entry['is_available'] ? 'success' : 'danger' ?>"><?= (int) $entry['is_available'] ? 'Available' : 'Blocked' ?></span></td>
                                    <td><?= $entry['custom_price'] !== null ? formatPrice((float) $entry['custom_price']) : '<span class="text-muted">Normal pricing</span>' ?></td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="availability_id" value="<?= $entry['id'] ?>">
                                            <input type="hidden" name="room_id" value="<?= $roomId ?>">
                                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($entries)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No upcoming overrides for this room.</td></tr>
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
<script>
document.getElementById('availabilitySelect')?.addEventListener('change', function () {
    const customPrice = document.getElementById('customPriceInput');
    customPrice.disabled = this.value === '0';
    if (customPrice.disabled) customPrice.value = '';
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
