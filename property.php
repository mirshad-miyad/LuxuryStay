<?php
require_once __DIR__ . '/includes/auth.php';
$db = getDB();
ensureOwnerFeatureSchema($db);

$id = (int) ($_GET['id'] ?? 0);
if (!$id) redirect(APP_URL . '/properties.php');

$stmt = $db->prepare("SELECT p.*, o.name as owner_name, o.phone as owner_phone, o.email as owner_email FROM properties p JOIN owners o ON p.owner_id = o.id WHERE p.id = ? AND p.status = 'approved' AND p.is_active = 1 AND p.deleted_at IS NULL");
$stmt->execute([$id]);
$property = $stmt->fetch();
if (!$property) {
    flash('danger', 'Property not found.');
    redirect(APP_URL . '/properties.php');
}

if (!empty($_SESSION['user_id'])) {
    recordRecentlyViewed($db, $_SESSION['user_id'], $id);
}

$images = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
$images->execute([$id]);
$images = $images->fetchAll();

$amenities = $db->prepare("SELECT a.* FROM amenities a JOIN property_amenities pa ON a.id = pa.amenity_id WHERE pa.property_id = ?");
$amenities->execute([$id]);
$amenities = $amenities->fetchAll();

$rooms = $db->prepare("SELECT r.*, GROUP_CONCAT(a.name ORDER BY a.name SEPARATOR ', ') AS room_amenities
    FROM rooms r
    LEFT JOIN room_amenities ra ON ra.room_id = r.id
    LEFT JOIN amenities a ON a.id = ra.amenity_id
    WHERE r.property_id = ? AND r.status = 'active'
    GROUP BY r.id
    ORDER BY r.price_per_night");
$rooms->execute([$id]);
$rooms = $rooms->fetchAll();

$reviews = $db->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 10");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = (int) ($_GET['guests'] ?? 1);

// Legacy Google Maps `pb` embed URLs can expire or be malformed. Generate the
// embed request from the property's own location data instead.
$latitude = filter_var($property['latitude'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($property['longitude'], FILTER_VALIDATE_FLOAT);
if ($latitude !== false && $longitude !== false && $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
    $mapQuery = $latitude . ',' . $longitude;
} else {
    $mapQuery = trim(implode(', ', array_filter([
        $property['address'] ?? '',
        $property['city'] ?? '',
        $property['district'] ?? '',
        $property['province'] ?? '',
        'Sri Lanka',
    ])));
}
$mapUrl = $mapQuery !== '' ? 'https://www.google.com/maps?output=embed&q=' . rawurlencode($mapQuery) : '';

$pageTitle = $property['name'];
require_once __DIR__ . '/includes/header.php';
$mainImage = !empty($images) ? getPropertyPrimaryImage($images[0]['image_path']) : getPropertyPrimaryImage(null);
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>" class="text-gold">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/properties.php" class="text-gold">Properties</a></li>
            <li class="breadcrumb-item active text-white"><?= e($property['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <img id="propertyGalleryMain" src="<?= $mainImage ?>" class="gallery-main w-100" alt="<?= e($property['name']) ?>">
            <?php if (count($images) > 1): ?>
            <div class="row g-2 mt-2">
                <?php foreach ($images as $index => $img): ?>
                <?php $imageUrl = getPropertyPrimaryImage($img['image_path']); ?>
                <div class="col-6 col-sm-4 col-md-3">
                    <button type="button" class="property-gallery-thumb <?= $index === 0 ? 'active' : '' ?>" data-gallery-thumbnail data-image-src="<?= e($imageUrl) ?>" aria-label="View image <?= $index + 1 ?> of <?= e($property['name']) ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                        <img src="<?= e($imageUrl) ?>" class="img-fluid rounded" style="height:80px;object-fit:cover;" alt="<?= e($property['name']) ?> image <?= $index + 1 ?>">
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <div class="luxury-card p-4">
                <span class="badge bg-secondary"><?= e($property['property_type']) ?></span>
                <h1 class="h3 text-black mt-2"><?= e($property['name']) ?></h1>
                <p class="text-muted-light"><i class="bi bi-geo-alt text-gold"></i> <?= e($property['address']) ?>, <?= e($property['city'] ?: $property['district']) ?><?= $property['province'] ? ', ' . e($property['province']) : '' ?></p>
                <div class="rating-stars mb-3">
                    <?php $rating = max(0, min(5, (float) $property['avg_rating'])); ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?= $i <= floor($rating) ? 'bi-star-fill' : ($i - 0.5 <= $rating ? 'bi-star-half' : 'bi-star') ?>"></i>
                    <?php endfor; ?>
                    <span class="text-muted-light ms-1"><?= number_format($property['avg_rating'], 1) ?> (<?= (int)$property['review_count'] ?> reviews)</span>
                </div>
                <p class="text-muted-light"><?= nl2br(e($property['description'])) ?></p>
                <p class="text-muted-light small mb-1"><i class="bi bi-telephone text-gold"></i> <?= e($property['contact_phone'] ?: $property['owner_phone'] ?: 'Contact phone unavailable') ?></p>
                <p class="text-muted-light small mb-0"><i class="bi bi-envelope text-gold"></i> <?= e($property['contact_email'] ?: $property['owner_email'] ?: 'Contact email unavailable') ?></p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="luxury-card p-4 mb-4">
                <h4 class="text-gold mb-3">Amenities</h4>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($amenities as $am): ?>
                    <span class="amenity-badge"><i class="bi <?= e($am['icon']) ?> text-gold"></i> <?= e($am['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="luxury-card p-4 mb-4">
                <h4 class="text-gold mb-3">Available Rooms</h4>
                <?php foreach ($rooms as $room): 
                    $available = (!$checkIn || !$checkOut) ? true : isRoomAvailable($db, $room['id'], $checkIn, $checkOut);
                ?>
                <div class="border-bottom border-secondary py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="text-white mb-1"><?= e($room['name']) ?></h5>
                        <p class="small text-muted-light mb-0"><?= e($room['description']) ?></p>
                        <small class="text-muted-light"><i class="bi bi-people"></i> Max <?= $room['max_guests'] ?> guests &middot; <?= (int) $room['inventory'] ?> room(s) &middot; <?= e($room['bed_type']) ?> bed</small>
                        <?php if ($room['room_amenities']): ?><div><small class="text-muted-light"><?= e($room['room_amenities']) ?></small></div><?php endif; ?>
                    </div>
                    <div class="text-end">
                        <div class="property-price"><?= formatPrice($room['price_per_night']) ?>/night</div>
                        <?php if ($room['weekend_price']): ?><small class="text-muted-light">Weekend <?= formatPrice((float) $room['weekend_price']) ?></small><br><?php endif; ?>
                        <?php if ($available): ?>
                        <a href="<?= APP_URL ?>/booking.php?room_id=<?= $room['id'] ?>&check_in=<?= urlencode($checkIn) ?>&check_out=<?= urlencode($checkOut) ?>&guests=<?= $guests ?>" class="btn btn-gold btn-sm mt-2">Book Now</a>
                        <?php else: ?>
                        <span class="badge bg-danger mt-2">Not available</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($mapUrl): ?>
            <div class="luxury-card p-4 mb-4">
                <h4 class="text-gold mb-3">Location</h4>
                <div class="ratio ratio-16x9">
                    <iframe src="<?= e($mapUrl) ?>" title="Map showing <?= e($property['name']) ?>" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            <?php endif; ?>

            <div class="luxury-card p-4 mb-4">
                <h4 class="text-gold mb-3">Policies</h4>
                <p class="text-muted-light"><?= nl2br(e($property['policies'] ?? 'Standard check-in/out policies apply.')) ?></p>
            </div>

            <div class="luxury-card p-4">
                <h4 class="text-gold mb-3">Reviews</h4>
                <?php foreach ($reviews as $rev): ?>
                <div class="mb-3 pb-3 border-bottom border-secondary">
                    <div class="d-flex justify-content-between">
                        <strong><?= e($rev['user_name']) ?></strong>
                        <span class="rating-stars"><?php for ($i=1;$i<=5;$i++): ?><i class="bi bi-star<?= $i<=$rev['rating']?'-fill':'' ?>"></i><?php endfor; ?></span>
                    </div>
                    <p class="text-muted-light small mb-0"><?= e($rev['comment']) ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?><p class="text-muted-light">No reviews yet.</p><?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="luxury-card p-4 sticky-top" style="top:90px;">
                <h5 class="text-gold">Refine Search</h5>
                <form id="refineForm" class="mt-3">
                    <div class="mb-2">
                        <label class="form-label small">Check-in</label>
                        <input type="date" id="sideCheckIn" class="form-control form-control-sm" value="<?= e($checkIn) ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Check-out</label>
                        <input type="date" id="sideCheckOut" class="form-control form-control-sm" value="<?= e($checkOut) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Guests</label>
                        <input type="number" id="sideGuests" class="form-control form-control-sm" value="<?= $guests ?>" min="1">
                    </div>
                    <button type="button" class="btn btn-primary w-100 btn-sm" onclick="refinePropertySearch()">Update Availability</button>
                </form>
            </div>
        </div>
        <script>
        function refinePropertySearch() {
            const checkIn = document.getElementById('sideCheckIn').value;
            const checkOut = document.getElementById('sideCheckOut').value;
            const guests = document.getElementById('sideGuests').value;
            const baseUrl = '<?= APP_URL ?>/property.php?id=<?= $id ?>';
            const url = baseUrl + (checkIn ? '&check_in=' + encodeURIComponent(checkIn) : '') + (checkOut ? '&check_out=' + encodeURIComponent(checkOut) : '') + (guests ? '&guests=' + encodeURIComponent(guests) : '');
            window.location.href = url;
        }

        document.querySelectorAll('[data-gallery-thumbnail]').forEach(function (thumbnail) {
            thumbnail.addEventListener('click', function () {
                const mainImage = document.getElementById('propertyGalleryMain');
                if (!mainImage) return;
                mainImage.src = this.dataset.imageSrc;
                mainImage.alt = this.querySelector('img').alt;
                document.querySelectorAll('[data-gallery-thumbnail]').forEach(function (item) {
                    const active = item === thumbnail;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            });
        });
        </script>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
