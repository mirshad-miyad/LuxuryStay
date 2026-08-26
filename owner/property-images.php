<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];
$propertyId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$propertyId, $ownerId]);
$property = $stmt->fetch();
if (!$property) redirect(APP_URL . '/owner/properties.php');

$images = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC, id ASC");
$images->execute([$propertyId]);
$images = $images->fetchAll();

$pageTitle = 'Manage Images - ' . $property['name'];
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h1 class="mb-0">Manage <span class="text-gold">Images</span></h1>
                <a href="<?= APP_URL ?>/owner/properties.php" class="btn btn-outline-secondary">Back to Properties</a>
            </div>

            <div class="luxury-card p-4 mb-4">
                <h5 class="text-gold mb-2"><?= e($property['name']) ?></h5>
                <p class="text-muted mb-0"><?= e($property['address']) ?>, <?= e($property['city'] ?: $property['district']) ?></p>
            </div>

            <div class="luxury-card p-4 mb-4">
                <h5 class="text-gold mb-3"><i class="bi bi-cloud-upload me-2"></i>Upload New Images</h5>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Select Images</label>
                        <input type="file" id="imageInput" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                        <small class="text-muted d-block mt-1">Supported: JPG, PNG, WebP. Max 5MB per image.</small>
                    </div>
                    <div id="imagePreview" class="row g-2 mb-3"></div>
                    <div id="uploadStatus" class="mb-3"></div>
                    <button type="button" id="uploadBtn" class="btn btn-gold" onclick="uploadImages()">Upload Images</button>
                </form>
            </div>

            <div class="luxury-card p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="text-gold mb-0"><i class="bi bi-images me-2"></i>Current Images (<?= count($images) ?>)</h5>
                    <?php if (!empty($images)): ?>
                    <button type="button" id="saveOrderBtn" class="btn btn-outline-gold btn-sm" disabled onclick="saveImageOrder()"><i class="bi bi-check2 me-1"></i>Save Order</button>
                    <?php endif; ?>
                </div>

                <?php if (empty($images)): ?>
                    <p class="text-muted mb-0">No images uploaded yet.</p>
                <?php else: ?>
                    <div class="row g-3" id="imageGrid">
                        <?php foreach ($images as $index => $img): ?>
                        <div class="col-md-4 col-lg-3 image-item" data-image-id="<?= $img['id'] ?>">
                            <div class="image-card position-relative">
                                <img src="<?= getPropertyPrimaryImage($img['image_path']) ?>" class="w-100 rounded" style="height:200px;object-fit:cover;" alt="Property image">
                                <?php if ($img['is_primary']): ?>
                                <div class="badge bg-gold position-absolute top-0 start-0 m-2">Primary</div>
                                <?php endif; ?>
                                <div class="position-absolute bottom-0 start-0 end-0 p-2 bg-white bg-opacity-75 d-flex flex-wrap gap-1 justify-content-between">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" title="Move earlier" onclick="moveImage(this, -1)"><i class="bi bi-arrow-left"></i></button>
                                        <button type="button" class="btn btn-outline-secondary" title="Move later" onclick="moveImage(this, 1)"><i class="bi bi-arrow-right"></i></button>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$img['is_primary']): ?>
                                        <button type="button" class="btn btn-gold" onclick="setPrimary(<?= $img['id'] ?>)">Primary</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger" onclick="deleteImage(<?= $img['id'] ?>)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const maxUploadSize = <?= MAX_UPLOAD_SIZE ?>;

document.getElementById('imageInput')?.addEventListener('change', function () {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(function (file) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3';
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > maxUploadSize) {
            col.innerHTML = '<div class="alert alert-warning small mb-0">' + file.name + ' is not a valid image.</div>';
        } else {
            const img = document.createElement('img');
            img.className = 'w-100 rounded border';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.src = URL.createObjectURL(file);
            col.appendChild(img);
        }
        preview.appendChild(col);
    });
});

function uploadImages() {
    const input = document.getElementById('imageInput');
    const files = input.files;
    const status = document.getElementById('uploadStatus');

    if (files.length === 0) {
        status.innerHTML = '<div class="alert alert-warning">Please select at least one image.</div>';
        return;
    }

    const formData = new FormData();
    formData.append('property_id', <?= $propertyId ?>);
    formData.append('csrf', '<?= csrfToken() ?>');
    Array.from(files).forEach(file => formData.append('images[]', file));

    document.getElementById('uploadBtn').disabled = true;
    status.innerHTML = '<div class="alert alert-info">Uploading images...</div>';

    fetch('<?= APP_URL ?>/api/upload-property-image.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                status.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(() => location.reload(), 900);
            } else {
                status.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Upload failed') + '</div>';
                document.getElementById('uploadBtn').disabled = false;
            }
        })
        .catch(() => {
            status.innerHTML = '<div class="alert alert-danger">Error uploading images.</div>';
            document.getElementById('uploadBtn').disabled = false;
        });
}

function markOrderChanged() {
    const btn = document.getElementById('saveOrderBtn');
    if (btn) btn.disabled = false;
}

function moveImage(button, direction) {
    const item = button.closest('.image-item');
    const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;
    if (!sibling) return;
    if (direction < 0) {
        item.parentNode.insertBefore(item, sibling);
    } else {
        item.parentNode.insertBefore(sibling, item);
    }
    markOrderChanged();
}

function saveImageOrder() {
    const formData = new FormData();
    formData.append('property_id', <?= $propertyId ?>);
    formData.append('csrf', '<?= csrfToken() ?>');
    document.querySelectorAll('#imageGrid .image-item').forEach(item => {
        formData.append('order[]', item.dataset.imageId);
    });

    fetch('<?= APP_URL ?>/api/reorder-property-images.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save image order'));
            }
        })
        .catch(() => alert('Error saving image order'));
}

function setPrimary(imageId) {
    const body = new URLSearchParams({ image_id: imageId, property_id: <?= $propertyId ?>, csrf: '<?= csrfToken() ?>' });
    fetch('<?= APP_URL ?>/api/set-primary-image.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body
    })
    .then(response => response.json())
    .then(data => data.success ? location.reload() : alert('Error: ' + (data.message || 'Failed to set primary image')));
}

function deleteImage(imageId) {
    if (!confirm('Delete this image permanently?')) return;
    const body = new URLSearchParams({ image_id: imageId, property_id: <?= $propertyId ?>, csrf: '<?= csrfToken() ?>' });
    fetch('<?= APP_URL ?>/api/delete-property-image.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body
    })
    .then(response => response.json())
    .then(data => data.success ? location.reload() : alert('Error: ' + (data.message || 'Failed to delete image')));
}
</script>

<style>
.image-card {
    border: 1px solid rgba(59, 130, 230, 0.2);
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
}
.image-card:hover {
    border-color: #3B82E6;
    box-shadow: 0 0 15px rgba(59, 130, 230, 0.25);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
