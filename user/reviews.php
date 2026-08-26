<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $propertyId = (int) $_POST['property_id'];
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5) {
        $db->prepare("INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status) VALUES (?,?,?,?,?,'pending')")
            ->execute([$userId, $propertyId, $bookingId ?: null, $rating, $comment]);
        flash('success', 'Review submitted for approval.');
    }
    redirect(APP_URL . '/user/reviews.php');
}

$reviews = $db->prepare("SELECT r.*, p.name as property_name FROM reviews r JOIN properties p ON r.property_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$reviews->execute([$userId]);
$reviews = $reviews->fetchAll();

$bookingId = (int) ($_GET['booking_id'] ?? 0);
$bookingForReview = null;
if ($bookingId) {
    $stmt = $db->prepare("SELECT b.*, p.name FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ? AND b.user_id = ? AND b.status = 'completed'");
    $stmt->execute([$bookingId, $userId]);
    $bookingForReview = $stmt->fetch();
}

$pageTitle = 'My Reviews';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="dashboard-heading mb-4">
                <span class="section-label">Feedback</span>
                <h1 class="mb-1">My <span class="text-gold">Reviews</span></h1>
                <p class="text-muted-light mb-0">Ratings and comments from your completed stays.</p>
            </div>

            <?php if ($bookingForReview): ?>
            <div class="content-card p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                    <div>
                        <span class="section-label">New review</span>
                        <h5 class="mb-0"><?= e($bookingForReview['name']) ?></h5>
                    </div>
                    <span class="verified-badge"><i class="bi bi-check2-circle"></i> Verified Stay</span>
                </div>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="property_id" value="<?= (int) $bookingForReview['property_id'] ?>">
                    <input type="hidden" name="booking_id" value="<?= (int) $bookingId ?>">
                    <div class="col-md-4">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Comment</label>
                        <textarea name="comment" class="form-control" rows="3" required placeholder="Share the highlights of your stay"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-gold">Submit Review</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($reviews): ?>
            <div class="review-grid">
                <?php foreach ($reviews as $r):
                    $status = strtolower($r['status'] ?? 'pending');
                    $statusClass = [
                        'approved' => 'status-completed',
                        'pending' => 'status-pending',
                        'rejected' => 'status-cancelled',
                    ][$status] ?? 'status-pending';
                ?>
                <article class="review-card">
                    <div class="review-card-header">
                        <div>
                            <h5><?= e($r['property_name']) ?></h5>
                            <p><?= e(date('M j, Y', strtotime($r['created_at']))) ?></p>
                        </div>
                        <?php if (!empty($r['booking_id'])): ?>
                        <span class="verified-badge"><i class="bi bi-check2-circle"></i> Verified Stay</span>
                        <?php endif; ?>
                    </div>
                    <div class="rating-stars review-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?><i class="bi bi-star<?= $i <= (int) $r['rating'] ? '-fill' : '' ?>"></i><?php endfor; ?>
                    </div>
                    <p class="review-comment"><?= e($r['comment']) ?></p>
                    <span class="status-badge <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span>
                </article>
                <?php endforeach; ?>
            </div>
            <?php elseif (!$bookingForReview): ?>
            <div class="content-card p-4">
                <div class="empty-state">
                    <i class="bi bi-star"></i>
                    <h5>No reviews yet</h5>
                    <p>Completed stays can be reviewed from your bookings page.</p>
                    <a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-outline-gold btn-sm">View Bookings</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
