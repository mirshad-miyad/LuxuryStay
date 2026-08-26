<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $ownerId]);
$property = $stmt->fetch();
if (!$property) redirect(APP_URL . '/owner/properties.php');

$amenitiesList = $db->query("SELECT * FROM amenities ORDER BY name")->fetchAll();
$selected = $db->prepare("SELECT amenity_id FROM property_amenities WHERE property_id = ?");
$selected->execute([$id]);
$selectedAmenityIds = array_map('intval', array_column($selected->fetchAll(), 'amenity_id'));
$policyFields = parsePolicyText($property['policies'] ?? '');
$error = '';

$form = [
    'name' => $property['name'] ?? '',
    'description' => $property['description'] ?? '',
    'address' => $property['address'] ?? '',
    'city' => $property['city'] ?? '',
    'province' => $property['province'] ?? '',
    'district' => $property['district'] ?? '',
    'property_type' => $property['property_type'] ?? 'Hotel',
    'map_iframe' => $property['map_iframe'] ?? '',
    'contact_phone' => $property['contact_phone'] ?? '',
    'contact_email' => $property['contact_email'] ?? '',
    'latitude' => $property['latitude'] ?? '',
    'longitude' => $property['longitude'] ?? '',
    'custom_amenities' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        foreach ($form as $key => $value) {
            $form[$key] = trim((string) ($_POST[$key] ?? ''));
        }
        $policyFields = [];
        foreach (ownerPolicyLabels() as $key => $label) {
            $policyFields[$key] = trim((string) ($_POST['policy_' . $key] ?? ''));
        }
        $selectedAmenityIds = array_values(array_unique(array_map('intval', $_POST['amenities'] ?? [])));

        $errors = [];
        if ($form['name'] === '') $errors[] = 'Property name is required.';
        if ($form['address'] === '') $errors[] = 'Full address is required.';
        if ($form['city'] === '') $errors[] = 'City is required.';
        if (!in_array($form['district'], DISTRICTS, true)) $errors[] = 'Please select a valid district.';
        if ($form['province'] !== '' && !in_array($form['province'], PROVINCES, true)) $errors[] = 'Please select a valid province.';
        if (!in_array($form['property_type'], PROPERTY_TYPES, true)) $errors[] = 'Please select a valid property type.';
        if ($form['contact_email'] !== '' && !validateEmail($form['contact_email'])) $errors[] = 'Please enter a valid contact email.';

        $latitude = validateCoordinate($form['latitude'], -90, 90);
        $longitude = validateCoordinate($form['longitude'], -180, 180);
        if ($latitude === false) $errors[] = 'Latitude must be between -90 and 90.';
        if ($longitude === false) $errors[] = 'Longitude must be between -180 and 180.';

        $duplicate = $db->prepare("SELECT COUNT(*) FROM properties WHERE owner_id = ? AND id != ? AND deleted_at IS NULL AND LOWER(name) = LOWER(?) AND LOWER(address) = LOWER(?)");
        $duplicate->execute([$ownerId, $id, $form['name'], $form['address']]);
        if ((int) $duplicate->fetchColumn() > 0) {
            $errors[] = 'You already have another listing with this name and address.';
        }

        if (empty($errors)) {
            try {
                $db->beginTransaction();
                $policies = buildPolicyText($policyFields);
                $db->prepare("UPDATE properties SET
                    name = ?, description = ?, address = ?, city = ?, province = ?, district = ?, property_type = ?,
                    map_iframe = ?, contact_phone = ?, contact_email = ?, latitude = ?, longitude = ?, policies = ?
                    WHERE id = ? AND owner_id = ? AND deleted_at IS NULL")
                    ->execute([
                        $form['name'],
                        $form['description'],
                        $form['address'],
                        $form['city'],
                        $form['province'] ?: null,
                        $form['district'],
                        $form['property_type'],
                        $form['map_iframe'],
                        $form['contact_phone'],
                        $form['contact_email'],
                        $latitude,
                        $longitude,
                        $policies,
                        $id,
                        $ownerId,
                    ]);

                $customNames = preg_split('/[,;\r\n]+/', $form['custom_amenities']);
                foreach ($customNames as $amenityName) {
                    $amenityName = trim($amenityName);
                    if ($amenityName === '') continue;
                    $existing = $db->prepare("SELECT id FROM amenities WHERE LOWER(name) = LOWER(?) LIMIT 1");
                    $existing->execute([$amenityName]);
                    $amenityId = (int) ($existing->fetchColumn() ?: 0);
                    if (!$amenityId) {
                        $db->prepare("INSERT INTO amenities (name) VALUES (?)")->execute([$amenityName]);
                        $amenityId = (int) $db->lastInsertId();
                    }
                    $selectedAmenityIds[] = $amenityId;
                }

                $db->prepare("DELETE FROM property_amenities WHERE property_id = ?")->execute([$id]);
                $selectedAmenityIds = array_values(array_unique(array_filter($selectedAmenityIds)));
                foreach ($selectedAmenityIds as $amenityId) {
                    $db->prepare("INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?,?)")
                        ->execute([$id, $amenityId]);
                }

                $db->commit();
                flash('success', 'Property updated.');
                redirect(APP_URL . '/owner/properties.php');
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $error = 'Property could not be updated. Please review the form and try again.';
            }
        } else {
            $error = implode(' ', $errors);
        }
    }
}

$pageTitle = 'Edit Property';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
                <h1 class="mb-0">Edit <span class="text-gold">Property</span></h1>
                <div class="d-flex gap-2">
                    <a href="<?= APP_URL ?>/owner/property-images.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-images me-1"></i>Images</a>
                    <a href="<?= APP_URL ?>/owner/rooms.php?property_id=<?= $id ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-door-open me-1"></i>Rooms</a>
                </div>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <div class="form-dark">
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Property Name *</label><input type="text" name="name" class="form-control" value="<?= e($form['name']) ?>" required maxlength="200"></div>
                        <div class="col-md-3"><label class="form-label">Type *</label><select name="property_type" class="form-select" required><?php foreach (PROPERTY_TYPES as $t): ?><option value="<?= e($t) ?>" <?= $form['property_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">District / Region *</label><select name="district" class="form-select" required><?php foreach (DISTRICTS as $d): ?><option value="<?= e($d) ?>" <?= $form['district'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-8"><label class="form-label">Full Address *</label><input type="text" name="address" class="form-control" value="<?= e($form['address']) ?>" required maxlength="255"></div>
                        <div class="col-md-4"><label class="form-label">City *</label><input type="text" name="city" class="form-control" value="<?= e($form['city']) ?>" required maxlength="100"></div>
                        <div class="col-md-4"><label class="form-label">Province</label><select name="province" class="form-select"><option value="">Select province</option><?php foreach (PROVINCES as $province): ?><option value="<?= e($province) ?>" <?= $form['province'] === $province ? 'selected' : '' ?>><?= e($province) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Contact Phone</label><input type="tel" name="contact_phone" class="form-control" value="<?= e($form['contact_phone']) ?>" maxlength="30"></div>
                        <div class="col-md-4"><label class="form-label">Contact Email</label><input type="email" name="contact_email" class="form-control" value="<?= e($form['contact_email']) ?>" maxlength="150"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"><?= e($form['description']) ?></textarea></div>
                        <div class="col-md-6"><label class="form-label">Google Map Embed URL</label><input type="url" name="map_iframe" class="form-control" value="<?= e($form['map_iframe']) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Latitude</label><input type="number" name="latitude" class="form-control" value="<?= e((string) $form['latitude']) ?>" min="-90" max="90" step="0.000001"></div>
                        <div class="col-md-3"><label class="form-label">Longitude</label><input type="number" name="longitude" class="form-control" value="<?= e((string) $form['longitude']) ?>" min="-180" max="180" step="0.000001"></div>

                        <div class="col-12">
                            <label class="form-label">Amenities</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($amenitiesList as $a): ?>
                                <label class="amenity-badge"><input type="checkbox" name="amenities[]" value="<?= $a['id'] ?>" <?= in_array((int) $a['id'], $selectedAmenityIds, true) ? 'checked' : '' ?>> <?= e($a['name']) ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label">Add Custom Amenities</label><input type="text" name="custom_amenities" class="form-control" value="<?= e($form['custom_amenities']) ?>" placeholder="Add new amenities separated by commas"></div>

                        <?php foreach (ownerPolicyLabels() as $key => $label): ?>
                        <div class="col-md-6"><label class="form-label"><?= e($label) ?></label><textarea name="policy_<?= e($key) ?>" class="form-control" rows="2"><?= e($policyFields[$key] ?? '') ?></textarea></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-gold">Save Changes</button>
                        <a href="<?= APP_URL ?>/owner/properties.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
