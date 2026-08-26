<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/reporting.php';
require_once __DIR__ . '/invoice_template.php';

if (!isLoggedIn()) {
    flash('danger', 'Please login to download invoices.');
    redirect(APP_URL . '/login.php');
}

$db = getDB();
$bookingId = (int) ($_GET['id'] ?? 0);
$booking = $bookingId > 0 ? invoiceFetchBooking($db, $bookingId) : null;

if (!$booking || !invoiceCanAccess($booking)) {
    http_response_code(403);
    echo 'Invoice not found or access denied.';
    exit;
}

$html = renderInvoiceHtml($booking);
reportStreamPdf($html, 'luxurystay-invoice-' . $bookingId . '.pdf');
