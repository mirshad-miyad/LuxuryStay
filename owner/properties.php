<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $propertyId = (int) ($_POST['property_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $db->prepare("SELECT id, name, is_active FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
    $stmt->execute([$propertyId, $ownerId]);
    $property = $stmt->fetch();

    if ($property && in_array($action, ['deactivate', 'reactivate'], true)) {
        $active = $action === 'reactivate' ? 1 : 0;
        $db->prepare("UPDATE properties SET is_active = ? WHERE id = ? AND owner_id = ?")->execute([$active, $propertyId, $ownerId]);
        flash('success', $active ? 'Listing reactivated.' : 'Listing deactivated.');
    }
    redirect(APP_URL . '/owner/properties.php');
}

$statsStmt = $db->prepare("SELECT
    COUNT(*) AS total_count,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM properties WHERE owner_id = ? AND deleted_at IS NULL");
$statsStmt->execute([$ownerId]);
$stats = $statsStmt->fetch() ?: [];

$properties = $db->prepare("SELECT p.*,
        (SELECT COUNT(*) FROM rooms WHERE property_id = p.id) as room_count,
        (SELECT MIN(price_per_night) FROM rooms WHERE property_id = p.id AND status = 'active') as min_price,
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as image
    FROM properties p
    WHERE p.owner_id = ? AND p.deleted_at IS NULL
    ORDER BY p.created_at DESC");
$properties->execute([$ownerId]);
$properties = $properties->fetchAll();

$pageTitle = 'My Properties';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
                <h1 class="mb-0">My <span class="text-gold">Properties</span></h1>
                <a href="<?= APP_URL ?>/owner/add-property.php" class="btn btn-gold"><i class="bi bi-plus-circle me-1"></i>Add Property</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Total</div><div class="stat-value"><?= (int) ($stats['total_count'] ?? 0) ?></div></div></div>
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Active</div><div class="stat-value"><?= (int) ($stats['active_count'] ?? 0) ?></div></div></div>
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Inactive</div><div class="stat-value"><?= (int) ($stats['inactive_count'] ?? 0) ?></div></div></div>
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Pending</div><div class="stat-value"><?= (int) ($stats['pending_count'] ?? 0) ?></div></div></div>
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Approved</div><div class="stat-value"><?= (int) ($stats['approved_count'] ?? 0) ?></div></div></div>
                <div class="col-md-2 col-6"><div class="stat-card p-3"><div class="text-muted-light small">Rejected</div><div class="stat-value"><?= (int) ($stats['rejected_count'] ?? 0) ?></div></div></div>
            </div>

            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark align-middle">
                    <thead><tr><th>Property</th><th>Location</th><th>Type</th><th>Rooms</th><th>Pricing</th><th>Approval</th><th>Visibility</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($properties as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= getPropertyPrimaryImage($p['image']) ?>" alt="<?= e($p['name']) ?>" class="rounded" style="width:64px;height:48px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold"><?= e($p['name']) ?></div>
                                        <small class="text-muted"><?= e($p['contact_phone'] ?: 'No contact phone') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($p['city'] ?: $p['district']) ?><br><small class="text-muted"><?= e($p['district']) ?><?= $p['province'] ? ', ' . e($p['province']) : '' ?></small></td>
                            <td><?= e($p['property_type']) ?></td>
                            <td><?= (int) $p['room_count'] ?></td>
                            <td><?= $p['min_price'] !== null ? formatPrice((float) $p['min_price']) : '<span class="text-muted">No active rooms</span>' ?></td>
                            <td><span class="badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($p['status']) ?></span></td>
                            <td><span class="badge bg-<?= (int) $p['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="<?= APP_URL ?>/owner/edit-property.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold">Edit</a>
                                    <a href="<?= APP_URL ?>/owner/property-images.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Images</a>
                                    <a href="<?= APP_URL ?>/owner/rooms.php?property_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Rooms</a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
                                        <?php if ((int) $p['is_active']): ?>
                                        <button name="action" value="deactivate" class="btn btn-sm btn-outline-warning">Deactivate</button>
                                        <?php else: ?>
                                        <button name="action" value="reactivate" class="btn btn-sm btn-outline-success">Reactivate</button>
                                        <?php endif; ?>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-property" data-property-id="<?= $p['id'] ?>" data-property-name="<?= e($p['name']) ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($properties)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No properties yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePropertyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header border-gold">
                <h5 class="modal-title text-gold">Delete Property</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will hide <strong id="modalPropertyName"></strong> from owner and guest property lists.</p>
                <p class="small text-muted mb-0">Bookings, reviews, rooms, and images are kept for records.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Property</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let propertyIdToDelete = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePropertyModal'));

    document.querySelectorAll('.delete-property').forEach(btn => {
        btn.addEventListener('click', function() {
            propertyIdToDelete = this.dataset.propertyId;
            document.getElementById('modalPropertyName').textContent = this.dataset.propertyName;
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!propertyIdToDelete) return;

        const formData = new FormData();
        formData.append('csrf', '<?= csrfToken() ?>');
        formData.append('property_id', propertyIdToDelete);

        fetch('<?= APP_URL ?>/api/delete-property.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteModal.hide();
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + (data.message || 'Failed to delete property'));
            }
        })
        .catch(() => alert('Error deleting property'));
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
