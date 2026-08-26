<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/reporting.php';
requireRole('admin');

$db = getDB();
$filters = [
    'from' => reportFilterValue('from'),
    'to' => reportFilterValue('to'),
    'property_id' => reportFilterValue('property_id'),
    'owner_id' => reportFilterValue('owner_id'),
    'status' => reportFilterValue('status'),
];
[$whereSql, $params] = reportWhereClause($filters);

$properties = $db->query("SELECT id, name FROM properties ORDER BY name")->fetchAll();
$owners = $db->query("SELECT id, name, company_name FROM owners ORDER BY name")->fetchAll();

$stats = [
    'users' => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'owners' => (int) $db->query("SELECT COUNT(*) FROM owners")->fetchColumn(),
    'properties' => (int) $db->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
];

$stmt = $db->prepare("SELECT COUNT(*) AS bookings, COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END),0) AS revenue
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}");
$stmt->execute($params);
$bookingStats = $stmt->fetch() ?: [];
$stats['bookings'] = (int) ($bookingStats['bookings'] ?? 0);
$stats['revenue'] = (float) ($bookingStats['revenue'] ?? 0);

$stmt = $db->prepare("SELECT b.status, COUNT(*) AS total FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql} GROUP BY b.status");
$stmt->execute($params);
$statusSummary = array_column($stmt->fetchAll(), 'total', 'status');

$stmt = $db->prepare("SELECT DATE_FORMAT(b.created_at, '%Y-%m') AS month, COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END),0) AS revenue
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}
    GROUP BY DATE_FORMAT(b.created_at, '%Y-%m') ORDER BY month");
$stmt->execute($params);
$monthlyRevenue = $stmt->fetchAll();

$stmt = $db->prepare("SELECT p.name, COUNT(b.id) AS bookings, COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END),0) AS revenue
    FROM bookings b JOIN properties p ON b.property_id = p.id {$whereSql}
    GROUP BY p.id, p.name ORDER BY bookings DESC, revenue DESC LIMIT 10");
$stmt->execute($params);
$topProperties = $stmt->fetchAll();

$stmt = $db->prepare("SELECT o.name, o.company_name, COUNT(b.id) AS bookings, COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END),0) AS revenue
    FROM bookings b JOIN properties p ON b.property_id = p.id JOIN owners o ON p.owner_id = o.id {$whereSql}
    GROUP BY o.id, o.name, o.company_name ORDER BY revenue DESC, bookings DESC LIMIT 10");
$stmt->execute($params);
$topOwners = $stmt->fetchAll();

$stmt = $db->prepare("SELECT b.id, b.created_at, u.name AS customer, p.name AS property_name, o.name AS owner_name, b.status, b.payment_status, b.total_amount
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    JOIN owners o ON p.owner_id = o.id
    {$whereSql}
    ORDER BY b.created_at DESC LIMIT 500");
$stmt->execute($params);
$bookingRows = $stmt->fetchAll();

$exportRows = array_map(fn($b) => [
    $b['id'],
    date('Y-m-d', strtotime($b['created_at'])),
    $b['customer'],
    $b['property_name'],
    $b['owner_name'],
    ucfirst($b['status']),
    ucfirst($b['payment_status']),
    number_format((float) $b['total_amount'], 2),
], $bookingRows);
$exportHeaders = ['Booking ID', 'Date', 'Customer', 'Property', 'Owner', 'Status', 'Payment', 'Total'];
$summary = [
    'Users' => $stats['users'],
    'Owners' => $stats['owners'],
    'Properties' => $stats['properties'],
    'Filtered bookings' => $stats['bookings'],
    'Filtered revenue' => formatPrice($stats['revenue']),
];

if (($_GET['export'] ?? '') === 'excel') {
    reportStreamExcel('LuxuryStay Admin Report', $exportHeaders, $exportRows, 'luxurystay-admin-report.xls');
}
if (($_GET['export'] ?? '') === 'pdf') {
    reportStreamPdf(reportTablePdf('LuxuryStay Admin Report', $exportHeaders, $exportRows, $summary), 'luxurystay-admin-report.pdf');
}

$months = array_map(fn($r) => $r['month'], $monthlyRevenue);
$revenueValues = array_map(fn($r) => (float) $r['revenue'], $monthlyRevenue);
if (!$months) {
    $months = ['No data'];
    $revenueValues = [0];
}

$chartLabelsJson = json_encode($months, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$chartDataJson = json_encode($revenueValues, JSON_NUMERIC_CHECK) ?: '[]';
$extraInlineJs = [<<<JS
(function () {
    const chartEl = document.getElementById('monthlyRevenueChart');
    if (!chartEl || !window.Chart) return;
    new Chart(chartEl, {
        type: 'line',
        data: { labels: $chartLabelsJson, datasets: [{ label: 'Revenue', data: $chartDataJson, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .3 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
})();
JS];

$pageTitle = 'Admin Reports';
$dashRole = 'admin';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
$query = reportBuildQuery(['from', 'to', 'property_id', 'owner_id', 'status']);
require_once __DIR__ . '/includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                <h1 class="mb-0">Admin <span class="text-gold">Reports</span></h1>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary btn-sm" href="<?= APP_URL ?>/report_admin.php?<?= e($query) ?>&export=pdf"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                    <a class="btn btn-outline-success btn-sm" href="<?= APP_URL ?>/report_admin.php?<?= e($query) ?>&export=excel"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
                </div>
            </div>

            <form class="luxury-card p-3 mb-4" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" value="<?= e($filters['from']) ?>" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" value="<?= e($filters['to']) ?>" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Property</label><select name="property_id" class="form-select"><option value="">All</option><?php foreach ($properties as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int)$filters['property_id']===(int)$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Owner</label><select name="owner_id" class="form-select"><option value="">All</option><?php foreach ($owners as $o): ?><option value="<?= (int) $o['id'] ?>" <?= (int)$filters['owner_id']===(int)$o['id']?'selected':'' ?>><?= e($o['company_name'] ?: $o['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach (BOOKING_STATUSES as $s): ?><option value="<?= e($s) ?>" <?= $filters['status']===$s?'selected':'' ?>><?= e(ucfirst($s)) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2 d-flex gap-2"><button class="btn btn-gold flex-fill">Filter</button><a href="<?= APP_URL ?>/report_admin.php" class="btn btn-outline-secondary">Reset</a></div>
                </div>
            </form>

            <div class="row g-4 mb-4">
                <div class="col-md"><div class="stat-card"><div class="text-muted-light small">Users</div><div class="stat-value"><?= $stats['users'] ?></div></div></div>
                <div class="col-md"><div class="stat-card"><div class="text-muted-light small">Owners</div><div class="stat-value"><?= $stats['owners'] ?></div></div></div>
                <div class="col-md"><div class="stat-card"><div class="text-muted-light small">Properties</div><div class="stat-value"><?= $stats['properties'] ?></div></div></div>
                <div class="col-md"><div class="stat-card"><div class="text-muted-light small">Bookings</div><div class="stat-value"><?= $stats['bookings'] ?></div></div></div>
                <div class="col-md"><div class="stat-card"><div class="text-muted-light small">Revenue</div><div class="stat-value" style="font-size:1rem;"><?= formatPrice($stats['revenue']) ?></div></div></div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8"><div class="luxury-card p-4"><h5 class="text-gold">Monthly Revenue</h5><div class="bookings-chart-frame"><canvas id="monthlyRevenueChart"></canvas></div></div></div>
                <div class="col-lg-4"><div class="luxury-card p-4"><h5 class="text-gold mb-3">Booking Status Summary</h5><?php foreach (['pending','confirmed','cancelled','completed'] as $s): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?= e(ucfirst($s)) ?></span><strong><?= (int) ($statusSummary[$s] ?? 0) ?></strong></div><?php endforeach; ?></div></div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6"><div class="luxury-card p-4 table-responsive"><h5 class="text-gold mb-3">Top 10 Most Booked Properties</h5><table class="table table-dark table-sm mb-0"><thead><tr><th>Property</th><th>Bookings</th><th>Revenue</th></tr></thead><tbody><?php foreach ($topProperties as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= (int)$r['bookings'] ?></td><td><?= formatPrice((float)$r['revenue']) ?></td></tr><?php endforeach; ?><?php if (!$topProperties): ?><tr><td colspan="3" class="text-center text-muted">No data.</td></tr><?php endif; ?></tbody></table></div></div>
                <div class="col-lg-6"><div class="luxury-card p-4 table-responsive"><h5 class="text-gold mb-3">Top Earning Owners</h5><table class="table table-dark table-sm mb-0"><thead><tr><th>Owner</th><th>Bookings</th><th>Revenue</th></tr></thead><tbody><?php foreach ($topOwners as $r): ?><tr><td><?= e($r['company_name'] ?: $r['name']) ?></td><td><?= (int)$r['bookings'] ?></td><td><?= formatPrice((float)$r['revenue']) ?></td></tr><?php endforeach; ?><?php if (!$topOwners): ?><tr><td colspan="3" class="text-center text-muted">No data.</td></tr><?php endif; ?></tbody></table></div></div>
            </div>

            <div class="luxury-card p-4 table-responsive">
                <h5 class="text-gold mb-3">Filtered Booking History</h5>
                <table class="table table-dark table-sm mb-0">
                    <thead><tr><th>#</th><th>Date</th><th>Customer</th><th>Property</th><th>Owner</th><th>Status</th><th>Payment</th><th>Total</th><th></th></tr></thead>
                    <tbody><?php foreach ($bookingRows as $b): ?><tr><td><?= (int)$b['id'] ?></td><td><?= e(date('M j, Y', strtotime($b['created_at']))) ?></td><td><?= e($b['customer']) ?></td><td><?= e($b['property_name']) ?></td><td><?= e($b['owner_name']) ?></td><td><?= e(ucfirst($b['status'])) ?></td><td><?= e(ucfirst($b['payment_status'])) ?></td><td><?= formatPrice((float)$b['total_amount']) ?></td><td><a href="<?= APP_URL ?>/invoice.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-gold"><i class="bi bi-download"></i></a></td></tr><?php endforeach; ?><?php if (!$bookingRows): ?><tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr><?php endif; ?></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
