<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();

$stmt = $db->prepare("SELECT p.*, rv.viewed_at,
    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as image,
    (SELECT MIN(price_per_night) FROM rooms WHERE property_id = p.id) as min_price
    FROM recently_viewed rv
    JOIN properties p ON rv.property_id = p.id
    WHERE rv.user_id = ? AND p.status = 'approved'
    ORDER BY rv.viewed_at DESC
    LIMIT 12");
$stmt->execute([$_SESSION['user_id']]);
$properties = $stmt->fetchAll();

$pageTitle = 'Recently Viewed';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
                <div>
                    <span class="section-label">Browsing history</span>
                    <h1 class="mb-1">Recently <span class="text-gold">Viewed</span></h1>
                    <p class="text-muted-light mb-0">A quick return path to places that caught your eye.</p>
                </div>
                <a href="<?= APP_URL ?>/properties.php" class="btn btn-outline-gold btn-sm"><i class="bi bi-grid me-1"></i>Browse More</a>
            </div>

            <?php if ($properties): ?>
            <div class="recent-grid">
                <?php foreach ($properties as $prop):
                    $price = $prop['min_price'] !== null ? formatPrice((float) $prop['min_price']) : 'Price on request';
                ?>
                <article class="recent-card">
                    <a href="<?= APP_URL ?>/property.php?id=<?= (int) $prop['id'] ?>" class="recent-card-media">
                        <img src="<?= getPropertyPrimaryImage($prop['image'] ?? null) ?>" alt="<?= e($prop['name']) ?>">
                        <span class="recent-viewed-pill"><?= e(formatRelativeTime($prop['viewed_at'])) ?></span>
                    </a>
                    <div class="recent-card-body">
                        <div>
                            <h5><?= e($prop['name']) ?></h5>
                            <p><i class="bi bi-geo-alt"></i> <?= e($prop['district']) ?></p>
                        </div>
                        <div class="recent-card-footer">
                            <div>
                                <span>From</span>
                                <strong><?= e($price) ?></strong>
                                <small>per night</small>
                            </div>
                            <a href="<?= APP_URL ?>/property.php?id=<?= (int) $prop['id'] ?>" class="btn btn-sm btn-gold">View Property</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="content-card p-4">
                <div class="empty-state">
                    <i class="bi bi-clock-history"></i>
                    <h5>No recently viewed properties</h5>
                    <p>Browse stays and your latest views will appear here.</p>
                    <a href="<?= APP_URL ?>/properties.php" class="btn btn-gold btn-sm">Browse Stays</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
