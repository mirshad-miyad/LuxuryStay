<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/reporting.php';
requireRole('owner');

$db = getDB();
ensureOwnerFeatureSchema($db);
$ownerId = (int) $_SESSION['owner_id'];
$filters = [
    'from' => reportFilterValue('from'),
    'to' => reportFilterValue('to'),
    'property_id' => reportFilterValue('property_id'),
];

$where = ['p.owner_id = ?'];
$params = [$ownerId];
if ($filters['from'] !== '') {
    $where[] = 'DATE(b.created_at) >= ?';
    $params[] = $filters['from'];
}
if ($filters['to'] !== '') {
    $where[] = 'DATE(b.created_at) <= ?';
    $params[] = $filters['to'];
}
if ($filters['property_id'] !== '') {
    $where[] = 'b.property_id = ?';
    $params[] = (int) $filters['property_id'];
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$propertiesStmt = $db->prepare("SELECT id, name FROM properties WHERE owner_id = ? AND deleted_at IS NULL ORDER BY name");
$propertiesStmt->execute([$ownerId]);
$properties = $propertiesStmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) AS bookings,
    COALESCE(SUM(CASE WHEN b.payment_status = 'paid' AND b.status IN ('confirmed','completed') THEN b.total_amount ELSE 0 END),0) AS revenue,
    COALESCE(SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END),0) AS pending_bookings
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}");
$stmt->execute($params);
$summary = $stmt->fetch() ?: [];

$periodStart = $filters['from'] !== '' ? $filters['from'] : date('Y-01-01');
$periodEnd = $filters['to'] !== '' ? $filters['to'] : date('Y-m-d');
$periodDays = max(1, nightsBetween($periodStart, date('Y-m-d', strtotime($periodEnd . ' +1 day'))));

$roomParams = [$ownerId];
$roomPropertySql = '';
if ($filters['property_id'] !== '') {
    $roomPropertySql = ' AND p.id = ?';
    $roomParams[] = (int) $filters['property_id'];
}
$roomStmt = $db->prepare("SELECT COALESCE(SUM(r.inventory),0) FROM rooms r JOIN properties p ON r.property_id = p.id WHERE p.owner_id = ? AND p.deleted_at IS NULL {$roomPropertySql}");
$roomStmt->execute($roomParams);
$roomInventory = max(0, (int) $roomStmt->fetchColumn());

$occParams = [$ownerId, $periodStart, $periodEnd, $periodStart, $periodStart, $periodEnd, $periodEnd];
$occPropertySql = '';
if ($filters['property_id'] !== '') {
    $occPropertySql = ' AND b.property_id = ?';
    $occParams[] = (int) $filters['property_id'];
}
$occStmt = $db->prepare("SELECT COALESCE(SUM(DATEDIFF(LEAST(b.check_out, DATE_ADD(?, INTERVAL 1 DAY)), GREATEST(b.check_in, ?))),0)
    FROM bookings b JOIN properties p ON b.property_id = p.id
    WHERE p.owner_id = ? AND b.status IN ('confirmed','completed')
    AND b.check_in <= ? AND b.check_out > ? {$occPropertySql}");
$occStmt->execute([$periodEnd, $periodStart, $ownerId, $periodEnd, $periodStart, ...array_slice($occParams, 7)]);
$bookedNights = max(0, (int) $occStmt->fetchColumn());
$availableNights = $roomInventory * $periodDays;
$occupancy = $availableNights > 0 ? min(100, ($bookedNights / $availableNights) * 100) : 0;

$stmt = $db->prepare("SELECT DATE_FORMAT(b.created_at, '%Y-%m') AS month, COUNT(*) AS bookings
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}
    GROUP BY DATE_FORMAT(b.created_at, '%Y-%m') ORDER BY month");
$stmt->execute($params);
$monthlyBookings = $stmt->fetchAll();

$stmt = $db->prepare("SELECT p.name, COUNT(b.id) AS bookings, COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END),0) AS income
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}
    GROUP BY p.id, p.name ORDER BY income DESC, bookings DESC");
$stmt->execute($params);
$propertyIncome = $stmt->fetchAll();

$stmt = $db->prepare("SELECT b.id, b.created_at, u.name AS customer, p.name AS property_name, r.name AS room_name, b.check_in, b.check_out, b.status, b.payment_status, b.total_amount
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    JOIN rooms r ON b.room_id = r.id
    {$whereSql}
    ORDER BY b.created_at DESC LIMIT 500");
$stmt->execute($params);
$bookingHistory = $stmt->fetchAll();

$exportHeaders = ['Booking ID', 'Date', 'Customer', 'Property', 'Room', 'Check-in', 'Check-out', 'Status', 'Payment', 'Total'];
$exportRows = array_map(fn($b) => [
    $b['id'],
    date('Y-m-d', strtotime($b['created_at'])),
    $b['customer'],
    $b['property_name'],
    $b['room_name'],
    $b['check_in'],
    $b['check_out'],
    ucfirst($b['status']),
    ucfirst($b['payment_status']),
    number_format((float) $b['total_amount'], 2),
], $bookingHistory);
$exportSummary = [
    'Revenue' => formatPrice((float) ($summary['revenue'] ?? 0)),
    'Bookings' => (int) ($summary['bookings'] ?? 0),
    'Occupancy' => number_format($occupancy, 1) . '%',
    'Period' => $periodStart . ' to ' . $periodEnd,
];

if (($_GET['export'] ?? '') === 'excel') {
    reportStreamExcel('LuxuryStay Owner Report', $exportHeaders, $exportRows, 'luxurystay-owner-report.xls');
}
if (($_GET['export'] ?? '') === 'pdf') {
    reportStreamPdf(reportTablePdf('LuxuryStay Owner Report', $exportHeaders, $exportRows, $exportSummary), 'luxurystay-owner-report.pdf');
}

$months = array_map(fn($r) => $r['month'], $monthlyBookings);
$bookingCounts = array_map(fn($r) => (int) $r['bookings'], $monthlyBookings);
if (!$months) {
    $months = ['No data'];
    $bookingCounts = [0];
}
$chartLabelsJson = json_encode($months, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$chartDataJson = json_encode($bookingCounts, JSON_NUMERIC_CHECK) ?: '[]';
$extraInlineJs = [<<<JS
(function () {
    const chartEl = document.getElementById('ownerBookingsChart');
    if (!chartEl || !window.Chart) return;
    new Chart(chartEl, {
        type: 'bar',
        data: { labels: $chartLabelsJson, datasets: [{ label: 'Bookings', data: $chartDataJson, backgroundColor: 'rgba(37,99,235,.72)', borderColor: '#2563eb', borderWidth: 1, borderRadius: 8 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
})();
JS];

$pageTitle = 'Owner Reports';
$dashRole = 'owner';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
$query = reportBuildQuery(['from', 'to', 'property_id']);
require_once __DIR__ . '/includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                <h1 class="mb-0">Owner <span class="text-gold">Reports</span></h1>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary btn-sm" href="<?= APP_URL ?>/report_owner.php?<?= e($query) ?>&export=pdf"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                    <a class="btn btn-outline-success btn-sm" href="<?= APP_URL ?>/report_owner.php?<?= e($query) ?>&export=excel"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
                </div>
            </div>

            <form class="luxury-card p-3 mb-4" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" value="<?= e($filters['from']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" value="<?= e($filters['to']) ?>" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Property</label><select name="property_id" class="form-select"><option value="">All properties</option><?php foreach ($properties as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int)$filters['property_id']===(int)$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2 d-flex gap-2"><button class="btn btn-gold flex-fill">Filter</button><a href="<?= APP_URL ?>/report_owner.php" class="btn btn-outline-secondary">Reset</a></div>
                </div>
            </form>

            <div class="row g-4 mb-4">
                <div class="col-md-3"><div class="stat-card"><div class="text-muted-light small">Revenue</div><div class="stat-value" style="font-size:1.15rem;"><?= formatPrice((float) ($summary['revenue'] ?? 0)) ?></div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted-light small">Bookings</div><div class="stat-value"><?= (int) ($summary['bookings'] ?? 0) ?></div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted-light small">Pending</div><div class="stat-value"><?= (int) ($summary['pending_bookings'] ?? 0) ?></div></div></div>
                <div class="col-md-3"><div class="stat-card"><div class="text-muted-light small">Occupancy</div><div class="stat-value"><?= number_format($occupancy, 1) ?>%</div></div></div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-7"><div class="luxury-card p-4 h-100"><h5 class="text-gold mb-3">Monthly Bookings</h5><div class="bookings-chart-frame"><canvas id="ownerBookingsChart"></canvas></div></div></div>
                <div class="col-lg-5"><div class="luxury-card p-4 h-100 table-responsive"><h5 class="text-gold mb-3">Property-wise Income</h5><table class="table table-dark table-sm mb-0"><thead><tr><th>Property</th><th>Bookings</th><th>Income</th></tr></thead><tbody><?php foreach ($propertyIncome as $row): ?><tr><td><?= e($row['name']) ?></td><td><?= (int)$row['bookings'] ?></td><td><?= formatPrice((float)$row['income']) ?></td></tr><?php endforeach; ?><?php if (!$propertyIncome): ?><tr><td colspan="3" class="text-center text-muted">No data.</td></tr><?php endif; ?></tbody></table></div></div>
            </div>

            <div class="luxury-card p-4 table-responsive">
                <h5 class="text-gold mb-3">Booking History</h5>
                <table class="table table-dark table-sm mb-0">
                    <thead><tr><th>#</th><th>Guest</th><th>Property</th><th>Room</th><th>Dates</th><th>Status</th><th>Payment</th><th>Total</th><th></th></tr></thead>
                    <tbody><?php foreach ($bookingHistory as $b): ?><tr><td><?= (int)$b['id'] ?></td><td><?= e($b['customer']) ?></td><td><?= e($b['property_name']) ?></td><td><?= e($b['room_name']) ?></td><td><?= e($b['check_in']) ?> to <?= e($b['check_out']) ?></td><td><?= e(ucfirst($b['status'])) ?></td><td><?= e(ucfirst($b['payment_status'])) ?></td><td><?= formatPrice((float)$b['total_amount']) ?></td><td><a href="<?= APP_URL ?>/invoice.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-gold"><i class="bi bi-download"></i></a></td></tr><?php endforeach; ?><?php if (!$bookingHistory): ?><tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr><?php endif; ?></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
