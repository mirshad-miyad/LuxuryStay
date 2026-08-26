<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

$stats = [
    'users' => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'owners' => (int) $db->query("SELECT COUNT(*) FROM owners")->fetchColumn(),
    'properties' => (int) $db->query("SELECT COUNT(*) FROM properties WHERE status='approved'")->fetchColumn(),
    'pending_props' => (int) $db->query("SELECT COUNT(*) FROM properties WHERE status='pending'")->fetchColumn(),
    'bookings' => (int) $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'revenue' => (float) $db->query("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE payment_status='paid'")->fetchColumn(),
];

$monthlyRows = $db->query("SELECT MONTHNAME(created_at) AS month, COUNT(*) AS booking_count
    FROM bookings
    GROUP BY YEAR(created_at), MONTH(created_at), MONTHNAME(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)")->fetchAll();
$statusRows = $db->query("SELECT status, COUNT(*) AS status_count
    FROM bookings
    GROUP BY status
    ORDER BY FIELD(status, 'confirmed', 'completed', 'pending', 'cancelled'), status")->fetchAll();

$months = [];
$monthlyBookings = [];
foreach ($monthlyRows as $row) {
    $month = trim((string) ($row['month'] ?? ''));
    if ($month === '') {
        continue;
    }

    $months[] = $month;
    $monthlyBookings[] = max(0, (int) ($row['booking_count'] ?? 0));
}

if (empty($months)) {
    $months = ['No data'];
    $monthlyBookings = [0];
}

$statusPalette = [
    'approved' => '#16a34a',
    'confirmed' => '#16a34a',
    'completed' => '#0ea5e9',
    'pending' => '#f59e0b',
    'rejected' => '#ef4444',
    'cancelled' => '#ef4444',
];
$statuses = [];
$statusCounts = [];
$statusColors = [];
foreach ($statusRows as $row) {
    $statusKey = strtolower(trim((string) ($row['status'] ?? 'unknown')));
    if ($statusKey === '') {
        $statusKey = 'unknown';
    }

    $statuses[] = ucfirst($statusKey);
    $statusCounts[] = max(0, (int) ($row['status_count'] ?? 0));
    $statusColors[] = $statusPalette[$statusKey] ?? '#64748b';
}

if (empty($statuses)) {
    $statuses = ['No data'];
    $statusCounts = [0];
    $statusColors = ['#dbeafe'];
}

$monthsJson = json_encode($months, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$monthlyBookingsJson = json_encode($monthlyBookings, JSON_NUMERIC_CHECK) ?: '[]';
$statusesJson = json_encode($statuses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$statusCountsJson = json_encode($statusCounts, JSON_NUMERIC_CHECK) ?: '[]';
$statusColorsJson = json_encode($statusColors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]';
$extraInlineJs = [<<<JS
(function () {
    const bookingsTimeEl = document.getElementById('bookingsTimeChart');
    const bookingStatusEl = document.getElementById('bookingStatusChart');

    if (!window.Chart) {
        return;
    }

    if (bookingsTimeEl) {
        new Chart(bookingsTimeEl, {
            type: 'bar',
            data: {
                labels: $monthsJson,
                datasets: [{
                    label: 'Bookings',
                    data: $monthlyBookingsJson,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.72)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 52
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
    }

    if (bookingStatusEl) {
        new Chart(bookingStatusEl, {
            type: 'doughnut',
            data: {
                labels: $statusesJson,
                datasets: [{
                    data: $statusCountsJson,
                    backgroundColor: $statusColorsJson,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#1F2937',
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 16,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff'
                    }
                }
            }
        });
    }
})();
JS];

$pageTitle = 'Admin Dashboard';
$dashRole = 'admin';
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Admin <span class="text-gold">Dashboard</span></h1>
            <div class="row g-4 mb-4">
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Users</div><div class="stat-value"><?= $stats['users'] ?></div><span class="admin-stat-icon"><i class="bi bi-people"></i></span></div></div>
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Owners</div><div class="stat-value"><?= $stats['owners'] ?></div><span class="admin-stat-icon"><i class="bi bi-person-badge"></i></span></div></div>
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Properties</div><div class="stat-value"><?= $stats['properties'] ?></div><span class="admin-stat-icon"><i class="bi bi-buildings"></i></span></div></div>
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Pending</div><div class="stat-value"><?= $stats['pending_props'] ?></div><span class="admin-stat-icon"><i class="bi bi-clock-history"></i></span></div></div>
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Bookings</div><div class="stat-value"><?= $stats['bookings'] ?></div><span class="admin-stat-icon"><i class="bi bi-calendar-check"></i></span></div></div>
                <div class="col-md-2"><div class="stat-card admin-stat-card"><div class="text-muted-light small">Revenue</div><div class="stat-value" style="font-size:1rem;"><?= number_format($stats['revenue']/1000000, 1) ?>M</div><span class="admin-stat-icon"><i class="bi bi-cash-stack"></i></span></div></div>
            </div>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="luxury-card p-4">
                        <h5 class="text-gold">Bookings Over Time</h5>
                        <div class="bookings-chart-frame">
                            <canvas id="bookingsTimeChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="luxury-card p-4">
                        <h5 class="text-gold">Booking Status</h5>
                        <div class="bookings-chart-frame">
                            <canvas id="bookingStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
