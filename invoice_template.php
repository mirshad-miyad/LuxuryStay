<?php
function renderInvoiceHtml(array $booking): string
{
    $nights = nightsBetween($booking['check_in'], $booking['check_out']);
    $total = (float) $booking['total_amount'];
    $taxes = 0.00;
    $discount = 0.00;
    $subtotal = max(0, $total - $taxes + $discount);
    $invoiceNo = 'LS-' . str_pad((string) $booking['id'], 6, '0', STR_PAD_LEFT);

    ob_start();
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, Arial, sans-serif; background: #f8fafc; color: #0f172a; font-size: 12px; margin: 0; padding: 0; }
            .invoice-card { background: #fff; border: 1px solid #dbeafe; border-radius: 16px; padding: 28px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
            .topbar { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; }
            .brand-row { display: flex; align-items: center; gap: 12px; }
            .brand { color: #1d4ed8; font-size: 26px; font-weight: 800; }
            .brand-subtitle { color: #64748b; font-size: 11px; margin-top: 3px; }
            .muted { color: #64748b; }
            .invoice-title { text-align: right; color: #0f172a; font-size: 20px; font-weight: 700; }
            table { width: 100%; border-collapse: collapse; }
            .meta td { vertical-align: top; padding: 3px 0; }
            .section-title { color: #1d4ed8; font-weight: 700; margin: 20px 0 8px; font-size: 14px; }
            .box { border: 1px solid #dbeafe; border-radius: 10px; padding: 12px; background: #f8fbff; }
            .details th { background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1d4ed8; padding: 9px; text-align: left; border: 1px solid #bfdbfe; }
            .details td { padding: 9px; border: 1px solid #e2e8f0; }
            .totals td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
            .grand td { color: #1d4ed8; font-size: 16px; font-weight: 700; border-bottom: 0; }
            .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 11px; text-transform: uppercase; }
            .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #dbeafe; text-align: center; color: #1d4ed8; font-weight: 700; }
        </style>
    </head>
    <body>
        <div class="invoice-card">
            <table class="topbar">
                <tr>
                    <td>
                        <div class="brand-row">
                            <img src="<?= e(reportAssetDataUri('assets/images/favicon.svg')) ?>" alt="LuxuryStay" width="42" height="42" style="display:block;">
                            <div>
                                <div class="brand">LuxuryStay</div>
                                <div class="brand-subtitle">Sri Lanka's premier accommodation platform</div>
                            </div>
                        </div>
                    </td>
                    <td class="invoice-title">
                        INVOICE<br>
                        <span class="muted" style="font-size:12px;"><?= e($invoiceNo) ?></span>
                    </td>
                </tr>
            </table>

            <table class="meta">
                <tr>
                    <td style="width:50%;">
                        <div class="section-title">Customer Details</div>
                        <div class="box">
                            <strong><?= e($booking['customer_name']) ?></strong><br>
                            <?= e($booking['customer_email']) ?><br>
                            <?= e($booking['customer_phone'] ?: 'Phone not provided') ?>
                        </div>
                    </td>
                    <td style="width:50%; padding-left:18px;">
                        <div class="section-title">Booking Details</div>
                        <div class="box">
                            Invoice No: <strong><?= e($invoiceNo) ?></strong><br>
                            Booking ID: <strong>#<?= (int) $booking['id'] ?></strong><br>
                            Booking Date: <?= e(date('M j, Y', strtotime($booking['created_at']))) ?><br>
                            Booking Status: <span class="badge"><?= e($booking['status']) ?></span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Property Details</div>
            <div class="box">
                <strong><?= e($booking['property_name']) ?></strong><br>
                <?= e($booking['property_address']) ?><br>
                <?= e($booking['district']) ?> &middot; <?= e($booking['property_type']) ?>
            </div>

            <div class="section-title">Stay Summary</div>
            <table class="details">
                <thead>
                    <tr>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Guests</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= e($booking['room_name']) ?></td>
                        <td><?= e(date('M j, Y', strtotime($booking['check_in']))) ?></td>
                        <td><?= e(date('M j, Y', strtotime($booking['check_out']))) ?></td>
                        <td><?= $nights ?></td>
                        <td><?= (int) $booking['guests'] ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">Price Breakdown</div>
            <table class="totals">
                <tr><td>Room subtotal</td><td style="text-align:right;"><?= formatPrice($subtotal) ?></td></tr>
                <tr><td>Taxes</td><td style="text-align:right;"><?= formatPrice($taxes) ?></td></tr>
                <tr><td>Discounts</td><td style="text-align:right;"><?= $discount > 0 ? '-' . formatPrice($discount) : formatPrice(0) ?></td></tr>
                <tr class="grand"><td>Total Amount</td><td style="text-align:right;"><?= formatPrice($total) ?></td></tr>
            </table>

            <table style="margin-top:18px;">
                <tr>
                    <td>Payment Method: <strong><?= e($booking['payment_method'] ?: 'Not selected') ?></strong></td>
                    <td style="text-align:right;">Payment Status: <span class="badge"><?= e($booking['payment_status']) ?></span></td>
                </tr>
            </table>

            <div class="footer">Thank you for choosing LuxuryStay.</div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
