<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $id = (int) $_POST['property_id'];
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $db->prepare("SELECT p.name, p.owner_id FROM properties p WHERE p.id = ?");
        $stmt->execute([$id]);
        $prop = $stmt->fetch();
        if ($prop) {
            $db->prepare("UPDATE properties SET status = ? WHERE id = ?")->execute([$status, $id]);
            createNotification($db, 'Property ' . ucfirst($status), "Your property '{$prop['name']}' has been {$status}.", $status === 'approved' ? 'success' : 'warning', APP_URL . '/owner/properties.php', null, $prop['owner_id']);
            flash('success', 'Property ' . $status . '.');
        }
    }
    redirect(APP_URL . '/admin/properties.php');
}

$filter = $_GET['status'] ?? '';
$sql = "SELECT p.*, o.name as owner_name FROM properties p JOIN owners o ON p.owner_id = o.id";
if ($filter) $sql .= " WHERE p.status = " . $db->quote($filter);
$sql .= " ORDER BY p.created_at DESC";
$properties = $db->query($sql)->fetchAll();

$pageTitle = 'Manage Properties';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Manage <span class="text-gold">Properties</span></h1>
            <div class="mb-3">
                <a href="?status=pending" class="btn btn-sm btn-warning">Pending</a>
                <a href="?status=approved" class="btn btn-sm btn-success">Approved</a>
                <a href="?" class="btn btn-sm btn-outline-light">All</a>
            </div>
            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark">
                    <thead><tr><th>Name</th><th>Owner</th><th>District</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($properties as $p): ?>
                        <tr>
                            <td><?= e($p['name']) ?></td>
                            <td><?= e($p['owner_name']) ?></td>
                            <td><?= e($p['district']) ?></td>
                            <td><?= e($p['property_type']) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($p['status']) ?></span></td>
                            <td>
                                <?php if ($p['status'] === 'pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
                                    <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                    <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                                <?php endif; ?>
                                <a href="<?= APP_URL ?>/property.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold" target="_blank">View</a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-property" data-property-id="<?= $p['id'] ?>" data-property-name="<?= e($p['name']) ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePropertyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header border-gold">
                <h5 class="modal-title text-gold">Delete Property</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
                <p>Are you sure you want to delete <strong id="modalPropertyName"></strong>?</p>
                <p class="small text-muted">This will delete:</p>
                <ul class="small text-muted">
                    <li>The property and all its details</li>
                    <li>All rooms in this property</li>
                    <li>All bookings for these rooms</li>
                    <li>All reviews for this property</li>
                </ul>
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

    // Show confirmation modal
    document.querySelectorAll('.delete-property').forEach(btn => {
        btn.addEventListener('click', function() {
            propertyIdToDelete = this.dataset.propertyId;
            document.getElementById('modalPropertyName').textContent = this.dataset.propertyName;
            deleteModal.show();
        });
    });

    // Confirm deletion
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!propertyIdToDelete) return;

        const formData = new FormData();
        formData.append('csrf', document.querySelector('[name="csrf"]')?.value || '<?= csrfToken() ?>');
        formData.append('property_id', propertyIdToDelete);

        fetch('<?= APP_URL ?>/api/admin-delete-property.php', {
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
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting property');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
