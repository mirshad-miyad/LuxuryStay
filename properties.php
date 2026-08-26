<?php
require_once __DIR__ . '/includes/auth.php';
$db = getDB();
ensureOwnerFeatureSchema($db);

// Filters
$location = trim($_GET['location'] ?? '');
$district = trim($_GET['district'] ?? '');
$type = $_GET['type'] ?? '';
$minPrice = (float) ($_GET['min_price'] ?? 0);
$maxPrice = (float) ($_GET['max_price'] ?? 0);
$minRating = (float) ($_GET['min_rating'] ?? 0);
$amenities = $_GET['amenities'] ?? [];
if (!is_array($amenities)) {
    $amenities = array_filter(array_map('trim', explode(',', $amenities)));
}
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = (int) ($_GET['guests'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = ["p.status = 'approved'", "p.is_active = 1", "p.deleted_at IS NULL"];
$params = [];

if ($location) {
    $where[] = "(p.name LIKE ? OR p.district LIKE ? OR p.address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $params[] = "%$location%";
}
if ($district) {
    $where[] = "p.district = ?";
    $params[] = $district;
}
if ($type) {
    $where[] = "p.property_type = ?";
    $params[] = $type;
}
if ($minRating > 0) {
    $where[] = "p.avg_rating >= ?";
    $params[] = $minRating;
}

$sqlBase = "FROM properties p 
    LEFT JOIN (SELECT property_id, MIN(price_per_night) as min_price FROM rooms WHERE status='active' GROUP BY property_id) r ON r.property_id = p.id
    WHERE " . implode(' AND ', $where);

if ($minPrice > 0) {
    $sqlBase .= " AND r.min_price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $sqlBase .= " AND r.min_price <= ?";
    $params[] = $maxPrice;
}

if (!empty($amenities)) {
    foreach ($amenities as $am) {
        $sqlBase .= " AND EXISTS (SELECT 1 FROM property_amenities pa JOIN amenities a ON pa.amenity_id = a.id WHERE pa.property_id = p.id AND a.name LIKE ?)";
        $params[] = "%$am%";
    }
}

// Availability filter
if ($checkIn && $checkOut) {
    $sqlBase .= " AND EXISTS (
        SELECT 1 FROM rooms rm WHERE rm.property_id = p.id AND rm.status = 'active'
        AND rm.max_guests >= ?
        AND NOT EXISTS (
            SELECT 1 FROM room_availability ra WHERE ra.room_id = rm.id AND ra.is_available = 0
            AND ra.date >= ? AND ra.date < ?
        )
        AND NOT EXISTS (
            SELECT 1 FROM bookings b WHERE b.room_id = rm.id AND b.status IN ('pending','confirmed')
            AND b.check_in < ? AND b.check_out > ?
            GROUP BY b.room_id
            HAVING COUNT(*) >= rm.inventory
        )
    )";
    $params[] = max(1, $guests);
    $params[] = $checkIn;
    $params[] = $checkOut;
    $params[] = $checkOut;
    $params[] = $checkIn;
}

$countStmt = $db->prepare("SELECT COUNT(DISTINCT p.id) " . $sqlBase);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pager = paginate($total, $page);

$sql = "SELECT DISTINCT p.*, r.min_price,
    (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as image
    " . $sqlBase . " ORDER BY p.featured DESC, p.avg_rating DESC LIMIT {$pager['per_page']} OFFSET {$pager['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

$allAmenities = $db->query("SELECT * FROM amenities ORDER BY name")->fetchAll();

$pageTitle = 'Accommodations';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Find Your <span class="text-gold">Stay</span></h1>
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="filter-float sticky-top" style="top:90px;">
                <form method="GET" id="filterForm">
                    <h6 class="fw-bold">Filters</h6>
                    <div class="mb-3">
                        <label class="form-label small">Location / District</label>
                        <input type="text" name="location" class="form-control form-control-sm" value="<?= e($location) ?>">
                        <select name="district" class="form-select form-select-sm mt-1">
                            <option value="">All Districts</option>
                            <?php foreach (DISTRICTS as $d): ?>
                            <option value="<?= e($d) ?>" <?= $district === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Property Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach (PROPERTY_TYPES as $t): ?>
                            <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" id="priceRangeLabel">Price (LKR)</label>
                        <div class="row g-1">
                            <div class="col-6"><input type="number" name="min_price" id="priceMin" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice ?: '' ?>"></div>
                            <div class="col-6"><input type="number" name="max_price" id="priceMax" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice ?: '' ?>"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Min Rating</label>
                        <select name="min_rating" class="form-select form-select-sm">
                            <option value="0">Any</option>
                            <?php for ($r = 3; $r <= 5; $r++): ?>
                            <option value="<?= $r ?>" <?= $minRating == $r ? 'selected' : '' ?>><?= $r ?>+ stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Check-in / Check-out</label>
                        <input type="date" name="check_in" class="form-control form-control-sm mb-1" value="<?= e($checkIn) ?>">
                        <input type="date" name="check_out" class="form-control form-control-sm" value="<?= e($checkOut) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Guests</label>
                        <input type="number" name="guests" class="form-control form-control-sm" min="1" value="<?= $guests ?: 1 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Amenities</label>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($allAmenities as $am): ?>
                            <label class="amenity-chip <?= in_array($am['name'], $amenities) ? 'active' : '' ?>">
                                <input type="checkbox" name="amenities[]" value="<?= e($am['name']) ?>" <?= in_array($am['name'], $amenities) ? 'checked' : '' ?>>
                                <span><?= e($am['name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                    <a href="<?= APP_URL ?>/properties.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear</a>
                </form>
            </div>
        </div>
        <div class="col-lg-9">
            <p class="text-muted-light mb-3"><?= $total ?> properties found</p>
            <div class="row g-4">
                <?php foreach ($properties as $prop): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= APP_URL ?>/property.php?id=<?= $prop['id'] ?><?= $checkIn ? '&check_in='.urlencode($checkIn).'&check_out='.urlencode($checkOut).'&guests='.$guests : '' ?>" class="text-decoration-none">
                        <div class="luxury-card card h-100 overflow-hidden">
                            <div class="position-relative" style="height: 250px; overflow: hidden;">
                                <img src="<?= getPropertyPrimaryImage($prop['image']) ?>" class="card-img-top w-100" style="height:100%;object-fit:cover;" alt="<?= e($prop['name']) ?>">
                                <span class="badge bg-primary position-absolute top-2 start-2"><?= e($prop['property_type']) ?></span>
                                <span class="badge bg-danger position-absolute top-2 end-2" style="background: rgba(201, 162, 39, 0.9) !important;">
                                    <i class="bi bi-star-fill"></i> <?= round($prop['avg_rating'], 1) ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= e($prop['name']) ?></h5>
                                <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> <?= e($prop['city'] ?: $prop['district']) ?></p>
                                <p class="small text-muted mb-3"><?= e($prop['address'] ?? '') ?></p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <div class="rating-stars small">
                                        <?php $rating = max(0, min(5, (float) $prop['avg_rating'])); ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?= $i <= floor($rating) ? 'bi-star-fill' : ($i - 0.5 <= $rating ? 'bi-star-half' : 'bi-star') ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="property-price"><?= formatPrice($prop['min_price'] ?? 0) ?>/night</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($properties)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <p class="mt-3 text-muted-light">No properties match your criteria.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($pager['total_pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pager['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pager['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
