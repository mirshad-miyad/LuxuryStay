<?php
/**
 * Reporting, export, and invoice helpers.
 */

function reportFilterValue(string $key): string
{
    return trim((string) ($_GET[$key] ?? ''));
}

function reportBuildQuery(array $allowed = []): string
{
    $query = [];
    foreach (['from', 'to', 'property_id', 'owner_id', 'status'] as $key) {
        if (in_array($key, $allowed, true) && reportFilterValue($key) !== '') {
            $query[$key] = reportFilterValue($key);
        }
    }
    return http_build_query($query);
}

function reportWhereClause(array $filters, string $bookingAlias = 'b', string $propertyAlias = 'p'): array
{
    $where = [];
    $params = [];

    if (!empty($filters['from'])) {
        $where[] = "DATE({$bookingAlias}.created_at) >= ?";
        $params[] = $filters['from'];
    }
    if (!empty($filters['to'])) {
        $where[] = "DATE({$bookingAlias}.created_at) <= ?";
        $params[] = $filters['to'];
    }
    if (!empty($filters['property_id'])) {
        $where[] = "{$bookingAlias}.property_id = ?";
        $params[] = (int) $filters['property_id'];
    }
    if (!empty($filters['owner_id'])) {
        $where[] = "{$propertyAlias}.owner_id = ?";
        $params[] = (int) $filters['owner_id'];
    }
    if (!empty($filters['status']) && in_array($filters['status'], BOOKING_STATUSES, true)) {
        $where[] = "{$bookingAlias}.status = ?";
        $params[] = $filters['status'];
    }

    return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
}

function reportDompdfAutoloaded(): bool
{
    if (class_exists(\Dompdf\Dompdf::class)) {
        return true;
    }

    foreach ([ROOT_PATH . 'vendor/autoload.php', __DIR__ . '/../vendor/autoload.php'] as $autoload) {
        if (file_exists($autoload)) {
            require_once $autoload;
            break;
        }
    }

    return class_exists(\Dompdf\Dompdf::class);
}

function reportAssetDataUri(string $assetPath): string
{
    $basePath = defined('ROOT_PATH') ? ROOT_PATH : __DIR__ . '/../';
    $fullPath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($assetPath, '/');

    if (!is_file($fullPath)) {
        return '';
    }

    $contents = file_get_contents($fullPath);
    if ($contents === false) {
        return '';
    }

    return 'data:' . mime_content_type($fullPath) . ';base64,' . base64_encode($contents);
}

function reportStreamPdf(string $html, string $filename): void
{
    if (!reportDompdfAutoloaded()) {
        http_response_code(500);
        echo 'Dompdf is not installed. Run: composer require dompdf/dompdf';
        exit;
    }

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}

function reportStreamExcel(string $title, array $headers, array $rows, string $filename): void
{
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $logo = reportAssetDataUri('assets/images/favicon.svg');
    $logoHtml = $logo ? '<img src="' . e($logo) . '" alt="LuxuryStay" width="28" height="28" style="vertical-align:middle;margin-right:8px;">' : '';

    echo "\xEF\xBB\xBF";
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;">';
    echo '<tr><th colspan="' . count($headers) . '" style="text-align:left;background:#eff6ff;color:#1d4ed8;font-size:16px;">' . $logoHtml . e($title) . '</th></tr><tr>';
    foreach ($headers as $header) {
        echo '<th>' . e($header) . '</th>';
    }
    echo '</tr>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . e((string) $cell) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

function reportTablePdf(string $title, array $headers, array $rows, array $summary = []): string
{
    ob_start();
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; font-size: 12px; margin: 0; padding: 0; background: #f8fafc; }
            .report-shell { padding: 22px; }
            .header-card { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08); }
            .brand-row { display: flex; align-items: center; gap: 12px; }
            .brand { color: #1d4ed8; font-size: 22px; font-weight: 800; }
            .brand-subtitle { color: #64748b; font-size: 11px; margin-top: 3px; }
            .muted { color: #64748b; }
            .summary { width: 100%; margin: 18px 0; border-collapse: collapse; background: #fff; border: 1px solid #dbeafe; }
            .summary td { border-bottom: 1px solid #eff6ff; padding: 8px; }
            .summary tr:last-child td { border-bottom: 0; }
            table.data { width: 100%; border-collapse: collapse; background: #fff; }
            table.data th { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 8px; text-align: left; }
            table.data td { border: 1px solid #e2e8f0; padding: 8px; }
            h1 { margin: 0 0 4px; font-size: 18px; color: #0f172a; }
        </style>
    </head>
    <body>
        <div class="report-shell">
            <div class="header-card">
                <div class="brand-row">
                    <img src="<?= e(reportAssetDataUri('assets/images/favicon.svg')) ?>" alt="LuxuryStay" width="40" height="40" style="display:block;">
                    <div>
                        <div class="brand">LuxuryStay</div>
                        <div class="brand-subtitle">Premium property reports and bookings</div>
                    </div>
                </div>
            </div>
            <h1><?= e($title) ?></h1>
            <p class="muted">Generated on <?= e(date('M j, Y g:i A')) ?></p>
        <?php if ($summary): ?>
        <table class="summary">
            <?php foreach ($summary as $label => $value): ?>
            <tr><td><strong><?= e($label) ?></strong></td><td><?= e((string) $value) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
            <table class="data">
                <thead><tr><?php foreach ($headers as $header): ?><th><?= e($header) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr><?php foreach ($row as $cell): ?><td><?= e((string) $cell) ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="<?= count($headers) ?>">No records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function invoiceFetchBooking(PDO $db, int $bookingId): ?array
{
    $stmt = $db->prepare("SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
        p.name AS property_name, p.address AS property_address, p.district, p.property_type, p.owner_id,
        r.name AS room_name, r.price_per_night, r.bed_type
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN properties p ON b.property_id = p.id
        JOIN rooms r ON b.room_id = r.id
        WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    return $booking ?: null;
}

function invoiceCanAccess(array $booking): bool
{
    $role = getUserRole();
    if ($role === 'admin') return true;
    if ($role === 'user') return (int) $booking['user_id'] === (int) ($_SESSION['user_id'] ?? 0);
    if ($role === 'owner') return (int) $booking['owner_id'] === (int) ($_SESSION['owner_id'] ?? 0);
    return false;
}
