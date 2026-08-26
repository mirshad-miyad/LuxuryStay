<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = $_SESSION['owner_id'];

$stmt = $db->prepare("SELECT
    COUNT(*) AS total_properties,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_properties,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive_properties,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_properties,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_properties,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_properties
    FROM properties WHERE owner_id = ? AND deleted_at IS NULL");
$stmt->execute([$ownerId]);
$propertyStats = $stmt->fetch() ?: [];

$stmt = $db->prepare("SELECT COUNT(*) FROM rooms r JOIN properties p ON r.property_id = p.id WHERE p.owner_id = ? AND p.deleted_at IS NULL");
$stmt->execute([$ownerId]);
$roomTypeCount = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings b JOIN properties p ON b.property_id = p.id WHERE p.owner_id = ? AND p.deleted_at IS NULL");
$stmt->execute([$ownerId]);
$bookingCount = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings b JOIN properties p ON b.property_id = p.id WHERE p.owner_id = ? AND p.deleted_at IS NULL AND b.status = 'pending'");
$stmt->execute([$ownerId]);
$pendingCount = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT SUM(b.total_amount) FROM bookings b JOIN properties p ON b.property_id = p.id WHERE p.owner_id = ? AND p.deleted_at IS NULL AND b.status IN ('confirmed','completed') AND b.payment_status = 'paid'");
$stmt->execute([$ownerId]);
$revenue = (float) ($stmt->fetchColumn() ?? 0);

$recentProperties = $db->prepare("SELECT p.*,
    (SELECT COUNT(*) FROM rooms WHERE property_id = p.id) AS room_count
    FROM properties p
    WHERE p.owner_id = ? AND p.deleted_at IS NULL
    ORDER BY p.updated_at DESC LIMIT 5");
$recentProperties->execute([$ownerId]);
$recentProperties = $recentProperties->fetchAll();

$bookingDateColumn = dbColumnExists($db, 'bookings', 'booking_date') ? 'COALESCE(b.booking_date, b.created_at)' : 'b.created_at';
$chartStmt = $db->prepare("SELECT MONTHNAME($bookingDateColumn) AS month, COUNT(*) AS booking_count
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    WHERE p.owner_id = ? AND p.deleted_at IS NULL AND b.status IN ('approved', 'confirmed', 'completed')
    GROUP BY MONTH($bookingDateColumn), MONTHNAME($bookingDateColumn)
    ORDER BY MONTH($bookingDateColumn)");
$chartStmt->execute([$ownerId]);
$bookingChartRows = $chartStmt->fetchAll();

$months = [];
$bookingCounts = [];
foreach ($bookingChartRows as $row) {
    $month = trim((string) ($row['month'] ?? ''));
    if ($month === '') {
        continue;
    }

    $months[] = $month;
    $bookingCounts[] = max(0, (int) ($row['booking_count'] ?? 0));
}

if (empty($months)) {
    $months = ['No data'];
    $bookingCounts = [0];
}

$chartLabelsJson = json_encode($months, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$chartDataJson = json_encode($bookingCounts, JSON_NUMERIC_CHECK) ?: '[]';
$extraInlineJs = [<<<JS
(function () {
    const chartEl = document.getElementById('bookingsChart');

    if (!chartEl || !window.Chart) {
        return;
    }

    new Chart(chartEl, {
        type: 'bar',
        data: {
            labels: $chartLabelsJson,
            datasets: [{
                label: 'Bookings',
                data: $chartDataJson,
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.72)',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 48
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    padding: 12,
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)'
                    },
                    ticks: {
                        color: '#6B7280',
                        precision: 0,
                        stepSize: 1
                    }
                }
            }
        }
    });
})();
JS];

$pageTitle = 'Owner Dashboard';
$dashRole = 'owner';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
                <h1 class="mb-0">Owner <span class="text-gold">Dashboard</span></h1>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= APP_URL ?>/owner/add-property.php" class="btn btn-gold btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Property</a>
                    <a href="<?= APP_URL ?>/owner/properties.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-building me-1"></i>Manage Listings</a>
                    <a href="<?= APP_URL ?>/owner/rooms.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-door-open me-1"></i>Rooms</a>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Total Properties</div><div class="stat-value"><?= (int) ($propertyStats['total_properties'] ?? 0) ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Active Listings</div><div class="stat-value"><?= (int) ($propertyStats['active_properties'] ?? 0) ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Inactive Listings</div><div class="stat-value"><?= (int) ($propertyStats['inactive_properties'] ?? 0) ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Room Types</div><div class="stat-value"><?= $roomTypeCount ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Total Bookings</div><div class="stat-value"><?= $bookingCount ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Pending Bookings</div><div class="stat-value"><?= $pendingCount ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Approved Listings</div><div class="stat-value"><?= (int) ($propertyStats['approved_properties'] ?? 0) ?></div></div></div>
                <div class="col-md-2"><div class="stat-card"><div class="text-muted-light small">Revenue</div><div class="stat-value" style="font-size:1rem;"><?= formatPrice($revenue) ?></div></div></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="luxury-card p-4 h-100">
                        <h5 class="text-gold mb-3">Bookings Chart</h5>
                        <div class="bookings-chart-frame">
                            <canvas id="bookingsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="luxury-card p-4 h-100">
                        <h5 class="text-gold mb-3">Listing Status Overview</h5>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between"><span>Pending approval</span><span class="badge bg-warning"><?= (int) ($propertyStats['pending_properties'] ?? 0) ?></span></div>
                            <div class="d-flex justify-content-between"><span>Approved</span><span class="badge bg-success"><?= (int) ($propertyStats['approved_properties'] ?? 0) ?></span></div>
                            <div class="d-flex justify-content-between"><span>Rejected</span><span class="badge bg-danger"><?= (int) ($propertyStats['rejected_properties'] ?? 0) ?></span></div>
                            <div class="d-flex justify-content-between"><span>Inactive by owner</span><span class="badge bg-secondary"><?= (int) ($propertyStats['inactive_properties'] ?? 0) ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="luxury-card p-4 table-responsive">
                        <h5 class="text-gold mb-3">Recent Properties</h5>
                        <table class="table table-dark table-sm align-middle mb-0">
                            <thead><tr><th>Name</th><th>Type</th><th>Status</th><th>Visibility</th><th>Rooms</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($recentProperties as $property): ?>
                                <tr>
                                    <td><?= e($property['name']) ?><br><small class="text-muted"><?= e($property['city'] ?: $property['district']) ?></small></td>
                                    <td><?= e($property['property_type']) ?></td>
                                    <td><span class="badge bg-<?= $property['status'] === 'approved' ? 'success' : ($property['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($property['status']) ?></span></td>
                                    <td><span class="badge bg-<?= (int) $property['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $property['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= (int) $property['room_count'] ?></td>
                                    <td class="text-end"><a href="<?= APP_URL ?>/owner/edit-property.php?id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-gold">Edit</a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentProperties)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No properties yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
